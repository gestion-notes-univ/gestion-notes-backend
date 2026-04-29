<?php

namespace App\Service;

use App\Entity\Deliberation;
use App\Entity\Etudiant;
use App\Entity\Semestre;
use Doctrine\ORM\EntityManagerInterface;

class DeliberationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAll(): array
    {
        return $this->em->getRepository(Deliberation::class)->findAll();
    }

    public function getOne(string $id): ?Deliberation
    {
        return $this->em->getRepository(Deliberation::class)->find($id);
    }

    public function getByEtudiant(Etudiant $etudiant): array
    {
        return $this->em->getRepository(Deliberation::class)
                        ->findBy(['etudiant' => $etudiant]);
    }

    public function calculer(Etudiant $etudiant, Semestre $semestre): Deliberation
    {
        // ✅ n.ue — propriété $ue dans Note.php (pas uniteEnseignement)
        // ✅ note_finale calculé par PostgreSQL GENERATED
        $notes = $this->em->createQuery('
            SELECT n FROM App\Entity\Note n
            JOIN n.ue ue
            WHERE n.etudiant = :etudiant
            AND ue.semestre = :semestre
            AND n.noteFinale IS NOT NULL
        ')
        ->setParameters([
            'etudiant' => $etudiant,
            'semestre' => $semestre,
        ])
        ->getResult();

        if (empty($notes)) {
            throw new \Exception('Aucune note trouvée pour cet étudiant dans ce semestre');
        }

        $totalPoints    = 0;
        $totalCoefs     = 0;
        $creditsValides = 0;

        foreach ($notes as $note) {
            // ✅ getUe() — propriété $ue dans Note.php (pas getUniteEnseignement())
            $coef         = $note->getUe()->getCoefficient();
            // ✅ getNoteFinale() — calculé par PostgreSQL (pas getValeur())
            $noteFinale   = $note->getNoteFinale();

            $totalPoints += $noteFinale * $coef;
            $totalCoefs  += $coef;

            // ✅ getUe()->getNoteMinimum() pour la validation des crédits
            if ($noteFinale >= $note->getUe()->getNoteMinimum()) {
                $creditsValides += $note->getUe()->getCreditsEcts();
            }
        }

        $moyenne = $totalCoefs > 0 ? round($totalPoints / $totalCoefs, 2) : 0;

        $decision = match(true) {
            $moyenne >= 10 => 'admis',
            $moyenne >= 8  => 'rattrapage',
            default        => 'ajourne',
        };

        // Mise à jour si délibération existante
        $existing = $this->em->getRepository(Deliberation::class)->findOneBy([
            'etudiant' => $etudiant,
            'semestre' => $semestre,
        ]);

        if ($existing) {
            $existing->setMoyenneGenerale($moyenne)
                     ->setCreditsValides($creditsValides)
                     ->setDecision($decision)
                     ->setDateDeliberation(new \DateTime()); // ✅ DateTime pas DateTimeImmutable

            $this->em->flush();
            return $existing;
        }

        $delib = new Deliberation();
        $delib->setEtudiant($etudiant)
              ->setSemestre($semestre)
              ->setMoyenneGenerale($moyenne)
              ->setCreditsValides($creditsValides)
              ->setDecision($decision)
              ->setDateDeliberation(new \DateTime()); // ✅ DateTime pas DateTimeImmutable

        $this->em->persist($delib);
        $this->em->flush();

        return $delib;
    }

    public function updateDecision(Deliberation $d, array $data): Deliberation
    {
        $valid = ['admis', 'ajourne', 'rattrapage', 'exclus'];

        if (!in_array($data['decision'] ?? '', $valid)) {
            throw new \Exception('Décision invalide. Valeurs acceptées : ' . implode(', ', $valid));
        }

        $d->setDecision($data['decision']);

        if (isset($data['credits_valides'])) {
            $d->setCreditsValides((int) $data['credits_valides']);
        }

        $this->em->flush();

        return $d;
    }

    public function serialize(Deliberation $d): array
    {
        $u = $d->getEtudiant()->getUtilisateur();

        return [
            'id'               => $d->getId(),
            // ✅ pas getNomComplet() — utilise getPrenom() + getNom()
            'etudiant'         => $u->getPrenom() . ' ' . $u->getNom(),
            'numero_etudiant'  => $d->getEtudiant()->getNumeroEtudiant(),
            'semestre'         => $d->getSemestre()->getNom(),
            'annee_academique' => $d->getSemestre()->getAnneeAcademique(),
            'moyenne_generale' => $d->getMoyenneGenerale(),
            'credits_valides'  => $d->getCreditsValides(),
            'decision'         => $d->getDecision(),
            'date_deliberation'=> $d->getDateDeliberation()?->format('d/m/Y H:i'),
        ];
    }
}
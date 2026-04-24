<?php

namespace App\Service;

use App\Entity\Deliberation;
use App\Entity\Etudiant;
use App\Entity\Semestre;
use App\Entity\Note;
use Doctrine\ORM\EntityManagerInterface;

class DeliberationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAll(): array
    {
        return $this->em->getRepository(Deliberation::class)->findAll();
    }

    public function getByEtudiant(Etudiant $etudiant): array
    {
        return $this->em->getRepository(Deliberation::class)
                        ->findBy(['etudiant' => $etudiant]);
    }

    public function calculer(Etudiant $etudiant, Semestre $semestre): Deliberation
    {
        $notes = $this->em->createQuery('
            SELECT n FROM App\Entity\Note n
            JOIN n.uniteEnseignement ue
            WHERE n.etudiant = :etudiant
            AND ue.semestre = :semestre
        ')
        ->setParameters([
            'etudiant' => $etudiant,
            'semestre' => $semestre
        ])
        ->getResult();

        if (empty($notes)) {
            throw new \Exception('Aucune note trouvée');
        }

        $totalPoints = 0;
        $totalCoefs = 0;
        $creditsValides = 0;

        foreach ($notes as $note) {
            $coef = $note->getUniteEnseignement()->getCoefficient();
            $totalPoints += $note->getValeur() * $coef;
            $totalCoefs += $coef;

            if ($note->getValeur() >= 10) {
                $creditsValides += $note->getUniteEnseignement()->getCreditsEcts();
            }
        }

        $moyenne = $totalCoefs > 0 ? round($totalPoints / $totalCoefs, 2) : 0;

        $decision = match(true) {
            $moyenne >= 10 => 'admis',
            $moyenne >= 8  => 'rattrapage',
            default        => 'ajourne'
        };

        $existing = $this->em->getRepository(Deliberation::class)->findOneBy([
            'etudiant' => $etudiant,
            'semestre' => $semestre
        ]);

        if ($existing) {
            $existing->setMoyenneGenerale($moyenne)
                     ->setCreditsValides($creditsValides)
                     ->setDecision($decision)
                     ->setDateDeliberation(new \DateTimeImmutable());

            $this->em->flush();
            return $existing;
        }

        $delib = new Deliberation();
        $delib->setEtudiant($etudiant)
              ->setSemestre($semestre)
              ->setMoyenneGenerale($moyenne)
              ->setCreditsValides($creditsValides)
              ->setDecision($decision)
              ->setDateDeliberation(new \DateTimeImmutable());

        $this->em->persist($delib);
        $this->em->flush();

        return $delib;
    }

    public function updateDecision(Deliberation $d, array $data): Deliberation
    {
        $valid = ['admis','ajourne','rattrapage','exclus'];

        if (!in_array($data['decision'] ?? '', $valid)) {
            throw new \Exception('Décision invalide');
        }

        $d->setDecision($data['decision']);

        if (isset($data['credits_valides'])) {
            $d->setCreditsValides($data['credits_valides']);
        }

        $this->em->flush();

        return $d;
    }

    public function serialize(Deliberation $d): array
    {
        return [
            'id' => $d->getId(),
            'etudiant' => $d->getEtudiant()->getUtilisateur()->getNomComplet(),
            'semestre' => $d->getSemestre()->getNom(),
            'moyenne' => $d->getMoyenneGenerale(),
            'credits' => $d->getCreditsValides(),
            'decision' => $d->getDecision()
        ];
    }
}
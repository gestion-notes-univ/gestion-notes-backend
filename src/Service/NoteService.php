<?php

namespace App\Service;

use App\Entity\Note;
use App\Entity\Etudiant;
use App\Entity\UniteEnseignement;
use Doctrine\ORM\EntityManagerInterface;

class NoteService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAllNotes(): array
    {
        return $this->em->getRepository(Note::class)->findAll();
    }

    public function getNotesByEtudiant(Etudiant $etudiant): array
    {
        return $this->em->getRepository(Note::class)
                        ->findBy(['etudiant' => $etudiant]);
    }

    public function getNotesByUe(UniteEnseignement $ue): array
    {
        return $this->em->getRepository(Note::class)
                        ->findBy(['uniteEnseignement' => $ue]);
    }

    public function createNote(array $data): Note
    {
        if ($data['valeur'] < 0 || $data['valeur'] > 20) {
            throw new \Exception('La note doit être entre 0 et 20');
        }

        $etudiant = $this->em->getRepository(Etudiant::class)->find($data['etudiant_id']);
        $ue       = $this->em->getRepository(UniteEnseignement::class)->find($data['unite_enseignement_id']);

        if (!$etudiant) throw new \Exception('Étudiant introuvable');
        if (!$ue) throw new \Exception('UE introuvable');

        $session = $data['session'] ?? 'normale';

        $exist = $this->em->getRepository(Note::class)->findOneBy([
            'etudiant' => $etudiant,
            'uniteEnseignement' => $ue,
            'session' => $session,
        ]);

        if ($exist) {
            throw new \Exception('Note déjà existante pour cette session');
        }

        $note = new Note();
        $note->setEtudiant($etudiant)
             ->setUniteEnseignement($ue)
             ->setValeur((float)$data['valeur'])
             ->setSession($session)
             ->setDateSaisie(new \DateTimeImmutable());

        $this->em->persist($note);
        $this->em->flush();

        return $note;
    }

    public function updateNote(Note $note, array $data): Note
    {
        if (isset($data['valeur'])) {
            if ($data['valeur'] < 0 || $data['valeur'] > 20) {
                throw new \Exception('Note invalide');
            }
            $note->setValeur((float)$data['valeur']);
        }

        if (isset($data['session'])) {
            $note->setSession($data['session']);
        }

        $this->em->flush();

        return $note;
    }

    public function deleteNote(Note $note): void
    {
        $this->em->remove($note);
        $this->em->flush();
    }

    public function calculMoyenne(array $notes): ?float
    {
        if (empty($notes)) return null;

        $total = 0;
        $coefTotal = 0;

        foreach ($notes as $note) {
            $coef = $note->getUniteEnseignement()->getCoefficient();
            $total += $note->getValeur() * $coef;
            $coefTotal += $coef;
        }

        return $coefTotal > 0 ? round($total / $coefTotal, 2) : null;
    }

    public function calculStats(array $notes): array
    {
        if (empty($notes)) return [];

        $valeurs = array_map(fn($n) => $n->getValeur(), $notes);

        return [
            'nombre' => count($valeurs),
            'moyenne' => round(array_sum($valeurs)/count($valeurs), 2),
            'min' => min($valeurs),
            'max' => max($valeurs),
        ];
    }

    public function serialize(Note $note): array
    {
        return [
            'id' => $note->getId(),
            'etudiant' => $note->getEtudiant()->getUtilisateur()->getNomComplet(),
            'ue' => $note->getUniteEnseignement()->getNom(),
            'valeur' => $note->getValeur(),
            'session' => $note->getSession(),
        ];
    }
}
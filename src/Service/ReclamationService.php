<?php

namespace App\Service;

use App\Entity\Reclamation;
use App\Entity\Etudiant;
use App\Entity\Note;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class ReclamationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAll(): array
    {
        return $this->em->getRepository(Reclamation::class)->findAll();
    }

    public function getByEtudiant(Utilisateur $user): array
    {
        $etudiant = $this->em->getRepository(Etudiant::class)
            ->findOneBy(['utilisateur' => $user]);

        if (!$etudiant) {
            throw new \Exception('Profil étudiant introuvable');
        }

        return $this->em->getRepository(Reclamation::class)
            ->findBy(['etudiant' => $etudiant]);
    }

    public function create(array $data, Utilisateur $user): Reclamation
    {
        if (empty($data['note_id']) || empty($data['motif'])) {
            throw new \Exception('Champs obligatoires : note_id, motif');
        }

        $etudiant = $this->em->getRepository(Etudiant::class)
            ->findOneBy(['utilisateur' => $user]);

        $note = $this->em->getRepository(Note::class)
            ->find($data['note_id']);

        if (!$etudiant) throw new \Exception('Profil étudiant introuvable');
        if (!$note) throw new \Exception('Note introuvable');

        if ($note->getEtudiant()->getId() != $etudiant->getId()) {
            throw new \Exception('Cette note ne vous appartient pas');
        }

        $existante = $this->em->getRepository(Reclamation::class)->findOneBy([
            'etudiant' => $etudiant,
            'note'     => $note,
            'statut'   => 'en_attente',
        ]);

        if ($existante) {
            throw new \Exception('Réclamation déjà en attente');
        }

        $reclamation = new Reclamation();
        $reclamation->setEtudiant($etudiant)
            ->setNote($note)
            ->setMotif($data['motif'])
            ->setStatut('en_attente')
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($reclamation);
        $this->em->flush();

        return $reclamation;
    }

    public function traiter(string $id, array $data, Utilisateur $user): Reclamation
    {
        $reclamation = $this->em->getRepository(Reclamation::class)->find($id);

        if (!$reclamation) {
            throw new \Exception('Réclamation introuvable');
        }

        $statutsValides = ['en_cours', 'resolue', 'rejetee'];
        if (!in_array($data['statut'] ?? '', $statutsValides)) {
            throw new \Exception('Statut invalide');
        }

        $reclamation->setStatut($data['statut'])
            ->setTraiteePar($user);

        $this->em->flush();

        return $reclamation;
    }
}
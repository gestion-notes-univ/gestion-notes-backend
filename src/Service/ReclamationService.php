<?php

namespace App\Service;

use App\Entity\Reclamation;
use App\Entity\Etudiant;
use App\Entity\Note;
use Doctrine\ORM\EntityManagerInterface;

class ReclamationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAll(): array
    {
        return $this->em->getRepository(Reclamation::class)->findAll();
    }

    public function getByEtudiant(Etudiant $etudiant): array
    {
        return $this->em->getRepository(Reclamation::class)
                        ->findBy(['etudiant' => $etudiant]);
    }

    public function create(Etudiant $etudiant, array $data): Reclamation
    {
        if (empty($data['note_id']) || empty($data['motif'])) {
            throw new \Exception('Champs obligatoires');
        }

        $note = $this->em->getRepository(Note::class)->find($data['note_id']);
        if (!$note) throw new \Exception('Note introuvable');

        // Vérifier appartenance
        if ($note->getEtudiant()->getId() != $etudiant->getId()) {
            throw new \Exception('Accès refusé');
        }

        // Vérifier existante
        $exist = $this->em->getRepository(Reclamation::class)->findOneBy([
            'etudiant' => $etudiant,
            'note' => $note,
            'statut' => 'en_attente'
        ]);

        if ($exist) {
            throw new \Exception('Réclamation déjà existante');
        }

        $r = new Reclamation();
        $r->setEtudiant($etudiant)
          ->setNote($note)
          ->setMotif($data['motif'])
          ->setStatut('en_attente')
          ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($r);
        $this->em->flush();

        return $r;
    }

    public function traiter(Reclamation $r, array $data): Reclamation
    {
        $valid = ['en_cours', 'resolue', 'rejetee'];

        if (!in_array($data['statut'] ?? '', $valid)) {
            throw new \Exception('Statut invalide');
        }

        $r->setStatut($data['statut'])
          ->setReponse($data['reponse'] ?? null);

        $this->em->flush();

        return $r;
    }

    public function serialize(Reclamation $r): array
    {
        return [
            'id' => $r->getId(),
            'etudiant' => $r->getEtudiant()->getUtilisateur()->getNomComplet(),
            'note' => [
                'id' => $r->getNote()->getId(),
                'valeur' => $r->getNote()->getValeur(),
            ],
            'motif' => $r->getMotif(),
            'statut' => $r->getStatut(),
            'reponse' => $r->getReponse()
        ];
    }
}
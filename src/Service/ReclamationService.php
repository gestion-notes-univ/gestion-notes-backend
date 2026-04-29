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

    public function getOne(string $id): ?Reclamation
    {
        return $this->em->getRepository(Reclamation::class)->find($id);
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
        $note     = $this->em->getRepository(Note::class)->find($data['note_id']);

        if (!$etudiant) throw new \Exception('Profil étudiant introuvable');
        if (!$note)     throw new \Exception('Note introuvable');

        if ($note->getEtudiant()->getId() != $etudiant->getId()) {
            throw new \Exception('Cette note ne vous appartient pas');
        }

        $existante = $this->em->getRepository(Reclamation::class)->findOneBy([
            'etudiant' => $etudiant,
            'note'     => $note,
            'statut'   => 'en_attente',
        ]);

        if ($existante) {
            throw new \Exception('Une réclamation est déjà en attente pour cette note');
        }

        $reclamation = new Reclamation();
        $reclamation->setEtudiant($etudiant)
                    ->setNote($note)
                    ->setMotif($data['motif'])
                    ->setStatut('en_attente');

        $this->em->persist($reclamation);
        $this->em->flush();

        return $reclamation;
    }

    public function traiter(Reclamation $reclamation, array $data, ?Utilisateur $user): Reclamation // a changer 
    {
        $statutsValides = ['en_cours', 'resolue', 'rejetee'];

        if (!in_array($data['statut'] ?? '', $statutsValides)) {
            throw new \Exception('Statut invalide. Valeurs acceptées : ' . implode(', ', $statutsValides));
        }

        $reclamation->setStatut($data['statut'])
                    ->setTraiteePar($user);

        $this->em->flush();

        return $reclamation;
    }

    public function serialize(Reclamation $r): array
    {
        $u = $r->getEtudiant()->getUtilisateur();

        return [
            'id'          => $r->getId(),
            'etudiant'    => $u->getPrenom() . ' ' . $u->getNom(),
            'note'        => [
                'id'          => $r->getNote()->getId(),
                'note_cc'     => $r->getNote()->getNoteCc(),
                'note_examen' => $r->getNote()->getNoteExamen(),
                'note_finale' => $r->getNote()->getNoteFinale(),
                'ue'          => $r->getNote()->getUe()->getNom(),
            ],
            'motif'       => $r->getMotif(),
            'statut'      => $r->getStatut(),
            'traitee_par' => $r->getTraiteePar()
                                ? $r->getTraiteePar()->getPrenom() . ' ' . $r->getTraiteePar()->getNom()
                                : null,
            'created_at'  => $r->getCreatedAt()?->format('d/m/Y H:i'),
        ];
    }
}
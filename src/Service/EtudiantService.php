<?php

namespace App\Service;

use App\Entity\Etudiant;
use App\Entity\Utilisateur;
use App\Entity\Filiere;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EtudiantService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {}

    public function getAll(?string $filiereId = null): array
    {
        if ($filiereId) {
            $filiere = $this->em->getRepository(Filiere::class)->find($filiereId);
            if (!$filiere) {
                throw new \Exception('Filière introuvable');
            }

            return $this->em->getRepository(Etudiant::class)
                            ->findBy(['filiere' => $filiere]);
        }

        return $this->em->getRepository(Etudiant::class)->findAll();
    }

    public function getOne(string $id): ?Etudiant
    {
        return $this->em->getRepository(Etudiant::class)->find($id);
    }

    public function create(array $data): Etudiant
    {
        $required = ['nom','prenom','email','password','numero_etudiant','annee_inscription','filiere_id'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Champ obligatoire : $field");
            }
        }

        // Vérifications
        if ($this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']])) {
            throw new \Exception('Email déjà utilisé');
        }

        if ($this->em->getRepository(Etudiant::class)->findOneBy(['numeroEtudiant' => $data['numero_etudiant']])) {
            throw new \Exception('Numéro étudiant déjà utilisé');
        }

        $filiere = $this->em->getRepository(Filiere::class)->find($data['filiere_id']);
        if (!$filiere) {
            throw new \Exception('Filière introuvable');
        }

        // Création utilisateur
        $user = new Utilisateur();
        $user->setNom($data['nom'])
             ->setPrenom($data['prenom'])
             ->setEmail($data['email'])
             ->setRole('etudiant')
             ->setPassword(
                 $this->hasher->hashPassword($user, $data['password'])
             );

        $this->em->persist($user);

        // Création étudiant
        $etudiant = new Etudiant();
        $etudiant->setUtilisateur($user)
                 ->setNumeroEtudiant($data['numero_etudiant'])
                 ->setAnneeInscription((int)$data['annee_inscription'])
                 ->setFiliere($filiere);

        $this->em->persist($etudiant);
        $this->em->flush();

        return $etudiant;
    }

    public function update(Etudiant $etudiant, array $data): Etudiant
    {
        if (!empty($data['filiere_id'])) {
            $filiere = $this->em->getRepository(Filiere::class)->find($data['filiere_id']);
            if (!$filiere) {
                throw new \Exception('Filière introuvable');
            }
            $etudiant->setFiliere($filiere);
        }

        if (!empty($data['annee_inscription'])) {
            $etudiant->setAnneeInscription((int)$data['annee_inscription']);
        }

        $user = $etudiant->getUtilisateur();

        if (!empty($data['nom']))    $user->setNom($data['nom']);
        if (!empty($data['prenom'])) $user->setPrenom($data['prenom']);
        if (!empty($data['email']))  $user->setEmail($data['email']);

        $this->em->flush();

        return $etudiant;
    }

    public function delete(Etudiant $etudiant): void
    {
        $this->em->remove($etudiant);
        $this->em->flush();
    }

    public function serialize(Etudiant $e): array
    {
        return [
            'id' => $e->getId(),
            'nom' => $e->getUtilisateur()->getNom(),
            'prenom' => $e->getUtilisateur()->getPrenom(),
            'email' => $e->getUtilisateur()->getEmail(),
            'numero_etudiant' => $e->getNumeroEtudiant(),
            'annee_inscription' => $e->getAnneeInscription(),
            'filiere' => [
                'id' => $e->getFiliere()?->getId(),
                'nom' => $e->getFiliere()?->getNom(),
                'code' => $e->getFiliere()?->getCode(),
            ],
        ];
    }
}
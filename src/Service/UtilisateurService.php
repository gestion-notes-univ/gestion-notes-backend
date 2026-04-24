<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {}

    public function getAll(?string $role = null): array
    {
        $criteria = $role ? ['role' => $role] : [];
        return $this->em->getRepository(Utilisateur::class)->findBy($criteria);
    }

    public function getOne(string $id): ?Utilisateur
    {
        return $this->em->getRepository(Utilisateur::class)->find($id);
    }

    public function create(array $data): Utilisateur
    {
        $required = ['nom', 'prenom', 'email', 'password', 'role'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Champ obligatoire : $field");
            }
        }

        $rolesValides = ['admin', 'enseignant', 'scolarite', 'etudiant'];

        if (!in_array($data['role'], $rolesValides)) {
            throw new \Exception('Rôle invalide');
        }

        $exist = $this->em->getRepository(Utilisateur::class)
                          ->findOneBy(['email' => $data['email']]);

        if ($exist) {
            throw new \Exception('Email déjà utilisé');
        }

        $user = new Utilisateur();
        $user->setNom($data['nom'])
             ->setPrenom($data['prenom'])
             ->setEmail($data['email'])
             ->setRole($data['role'])
             ->setPasswordHash(
                 $this->hasher->hashPassword($user, $data['password'])
             );

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function update(Utilisateur $user, array $data): Utilisateur
    {
        if (!empty($data['nom']))    $user->setNom($data['nom']);
        if (!empty($data['prenom'])) $user->setPrenom($data['prenom']);
        if (!empty($data['email']))  $user->setEmail($data['email']);
        if (!empty($data['role']))   $user->setRole($data['role']);
        if (isset($data['actif']))   $user->setActif((bool)$data['actif']);

        if (!empty($data['password'])) {
            $user->setPasswordHash(
                $this->hasher->hashPassword($user, $data['password'])
            );
        }

        $this->em->flush();

        return $user;
    }

    public function delete(Utilisateur $user, Utilisateur $currentUser): void
    {
        if ($user->getId() == $currentUser->getId()) {
            throw new \Exception('Suppression de son propre compte interdite');
        }

        $this->em->remove($user);
        $this->em->flush();
    }

    public function serialize(Utilisateur $u): array
    {
        return [
            'id'         => $u->getId(),
            'nom'        => $u->getNom(),
            'prenom'     => $u->getPrenom(),
            'email'      => $u->getEmail(),
            'role'       => $u->getRole(),
            'actif'      => $u->isActif(),
            'created_at' => $u->getCreatedAt()?->format('d/m/Y H:i'),
        ];
    }
}
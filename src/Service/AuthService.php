<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {}

    public function register(array $data): array
    {
        // Validation
        if (empty($data['email']) || empty($data['password']) || empty($data['nom']) || empty($data['prenom'])) {
            throw new \Exception('Champs obligatoires manquants');
        }

        // Vérifier email
        $exist = $this->em->getRepository(Utilisateur::class)
                          ->findOneBy(['email' => $data['email']]);

        if ($exist) {
            throw new \Exception('Email déjà utilisé');
        }

        // Vérifier rôle
        $role = $data['role'] ?? 'etudiant';
        $roles = ['admin', 'enseignant', 'scolarite', 'etudiant'];

        if (!in_array($role, $roles)) {
            throw new \Exception('Rôle invalide');
        }

        // Création utilisateur
        $user = new Utilisateur();
        $user->setNom($data['nom'])
             ->setPrenom($data['prenom'])
             ->setEmail($data['email'])
             ->setRole($role)
             ->setPassword($this->hasher->hashPassword($user, $data['password']));

        $this->em->persist($user);
        $this->em->flush();

        return [
            'id'    => $user->getId(),
            'nom'   => $user->getNom(),
            'email' => $user->getEmail(),
            'role'  => $user->getRole(),
        ];
    }
}

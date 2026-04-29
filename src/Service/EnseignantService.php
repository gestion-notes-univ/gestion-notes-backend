<?php

namespace App\Service;

use App\Entity\Enseignant;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EnseignantService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {}

    public function getAll(): array
    {
        return $this->em->getRepository(Enseignant::class)->findAll();
    }

    public function getById(string $id): ?Enseignant
    {
        return $this->em->getRepository(Enseignant::class)->find($id);
    }

    public function create(array $data): Enseignant
    {
        if ($this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']])) {
            throw new \Exception('Email déjà utilisé');
        }

        $user = new Utilisateur();
        $user->setNom($data['nom'])
             ->setPrenom($data['prenom'])
             ->setEmail($data['email'])
             ->setRole('enseignant')
             ->setPassword($this->hasher->hashPassword($user, $data['password']));

        $this->em->persist($user);

        $enseignant = new Enseignant();
        $enseignant->setUtilisateur($user)
                   ->setGrade($data['grade'] ?? null)
                   ->setSpecialite($data['specialite'] ?? null);

        $this->em->persist($enseignant);
        $this->em->flush();

        return $enseignant;
    }

    public function update(string $id, array $data): ?Enseignant
    {
        $enseignant = $this->getById($id);
        if (!$enseignant) return null;

        if (isset($data['grade']))      $enseignant->setGrade($data['grade']);
        if (isset($data['specialite'])) $enseignant->setSpecialite($data['specialite']);

        $user = $enseignant->getUtilisateur();
        if (!empty($data['nom']))    $user->setNom($data['nom']);
        if (!empty($data['prenom'])) $user->setPrenom($data['prenom']);
        if (!empty($data['email']))  $user->setEmail($data['email']);

        $this->em->flush();

        return $enseignant;
    }

    public function delete(string $id): bool
    {
        $enseignant = $this->getById($id);
        if (!$enseignant) return false;

        $this->em->remove($enseignant);
        $this->em->flush();

        return true;
    }
}
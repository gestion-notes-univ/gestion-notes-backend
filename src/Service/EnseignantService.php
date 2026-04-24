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

    public function getOne(string $id): ?Enseignant
    {
        return $this->em->getRepository(Enseignant::class)->find($id);
    }

    public function create(array $data): Enseignant
    {
        $required = ['nom', 'prenom', 'email', 'password'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Champ obligatoire : $field");
            }
        }

        if ($this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']])) {
            throw new \Exception('Email déjà utilisé');
        }

        $user = new Utilisateur();
        $user->setNom($data['nom'])
             ->setPrenom($data['prenom'])
             ->setEmail($data['email'])
             ->setRole('enseignant')
             ->setPasswordHash(
                 $this->hasher->hashPassword($user, $data['password'])
             );

        $this->em->persist($user);

        $enseignant = new Enseignant();
        $enseignant->setUtilisateur($user)
                   ->setGrade($data['grade'] ?? null)
                   ->setSpecialite($data['specialite'] ?? null);

        $this->em->persist($enseignant);
        $this->em->flush();

        return $enseignant;
    }

    public function update(Enseignant $enseignant, array $data): Enseignant
    {
        if (isset($data['grade']))      $enseignant->setGrade($data['grade']);
        if (isset($data['specialite'])) $enseignant->setSpecialite($data['specialite']);

        $user = $enseignant->getUtilisateur();

        if (!empty($data['nom']))    $user->setNom($data['nom']);
        if (!empty($data['prenom'])) $user->setPrenom($data['prenom']);
        if (!empty($data['email']))  $user->setEmail($data['email']);

        $this->em->flush();

        return $enseignant;
    }

    public function delete(Enseignant $enseignant): void
    {
        $this->em->remove($enseignant);
        $this->em->flush();
    }

    public function serialize(Enseignant $e): array
    {
        return [
            'id'         => $e->getId(),
            'nom'        => $e->getUtilisateur()->getNom(),
            'prenom'     => $e->getUtilisateur()->getPrenom(),
            'email'      => $e->getUtilisateur()->getEmail(),
            'grade'      => $e->getGrade(),
            'specialite' => $e->getSpecialite(),
        ];
    }
}
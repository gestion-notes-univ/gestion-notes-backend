<?php

namespace App\Service;

use App\Entity\Filiere;
use Doctrine\ORM\EntityManagerInterface;

class FiliereService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAll(): array
    {
        return $this->em->getRepository(Filiere::class)->findAll();
    }

    public function getById(string $id): ?Filiere
    {
        return $this->em->getRepository(Filiere::class)->find($id);
    }

    public function create(array $data): Filiere
    {
        if ($this->em->getRepository(Filiere::class)
            ->findOneBy(['code' => strtoupper($data['code'])])) {
            throw new \Exception('Code existe déjà');
        }

        $filiere = new Filiere();
        $filiere->setNom($data['nom'])
                ->setCode(strtoupper($data['code']))
                ->setDepartement($data['departement'] ?? null);

        $this->em->persist($filiere);
        $this->em->flush();

        return $filiere;
    }

    public function update(string $id, array $data): ?Filiere
    {
        $filiere = $this->getById($id);
        if (!$filiere) return null;

        if (!empty($data['nom']))        $filiere->setNom($data['nom']);
        if (!empty($data['code']))       $filiere->setCode(strtoupper($data['code']));
        if (isset($data['departement'])) $filiere->setDepartement($data['departement']);

        $this->em->flush();

        return $filiere;
    }

    public function delete(string $id): bool
    {
        $filiere = $this->getById($id);
        if (!$filiere) return false;

        $this->em->remove($filiere);
        $this->em->flush();

        return true;
    }
}
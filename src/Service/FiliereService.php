<?php

namespace App\Service;

use App\Entity\Filiere;
use Doctrine\ORM\EntityManagerInterface;

class FiliereService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getAll(): array
    {
        return $this->em->getRepository(Filiere::class)->findAll();
    }

    public function getOne(string $id): ?Filiere
    {
        return $this->em->getRepository(Filiere::class)->find($id);
    }

    public function create(array $data): Filiere
    {
        if (empty($data['nom']) || empty($data['code'])) {
            throw new \Exception('Champs obligatoires : nom, code');
        }

        if ($this->em->getRepository(Filiere::class)->findOneBy(['code' => $data['code']])) {
            throw new \Exception('Code filière déjà utilisé');
        }

        $filiere = new Filiere();
        $filiere->setNom($data['nom'])
                ->setCode(strtoupper($data['code']))
                ->setDepartement($data['departement'] ?? null);

        $this->em->persist($filiere);
        $this->em->flush();

        return $filiere;
    }

    public function update(Filiere $filiere, array $data): Filiere
    {
        if (!empty($data['nom']))        $filiere->setNom($data['nom']);
        if (!empty($data['code']))       $filiere->setCode(strtoupper($data['code']));
        if (array_key_exists('departement', $data)) {
            $filiere->setDepartement($data['departement']);
        }

        $this->em->flush();

        return $filiere;
    }

    public function delete(Filiere $filiere): void
    {
        $this->em->remove($filiere);
        $this->em->flush();
    }

    public function serialize(Filiere $f): array
    {
        return [
            'id'          => $f->getId(),
            'nom'         => $f->getNom(),
            'code'        => $f->getCode(),
            'departement' => $f->getDepartement(),
        ];
    }
}
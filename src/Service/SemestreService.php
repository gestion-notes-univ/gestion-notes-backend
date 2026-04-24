<?php

namespace App\Service;

use App\Entity\Semestre;
use App\Entity\Filiere;
use Doctrine\ORM\EntityManagerInterface;

class SemestreService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getAll(?string $filiereId = null, ?bool $actif = null): array
    {
        $criteria = [];

        if ($filiereId) {
            $filiere = $this->em->getRepository(Filiere::class)->find($filiereId);
            if (!$filiere) {
                throw new \Exception('Filière introuvable');
            }
            $criteria['filiere'] = $filiere;
        }

        if (!is_null($actif)) {
            $criteria['actif'] = $actif;
        }

        return $this->em->getRepository(Semestre::class)->findBy($criteria);
    }

    public function getOne(string $id): ?Semestre
    {
        return $this->em->getRepository(Semestre::class)->find($id);
    }

    public function create(array $data): Semestre
    {
        $required = ['nom', 'annee_academique', 'numero', 'filiere_id'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Champ obligatoire : $field");
            }
        }

        if ($data['numero'] < 1 || $data['numero'] > 8) {
            throw new \Exception('Numéro de semestre invalide');
        }

        $filiere = $this->em->getRepository(Filiere::class)->find($data['filiere_id']);
        if (!$filiere) {
            throw new \Exception('Filière introuvable');
        }

        $semestre = new Semestre();
        $semestre->setNom($data['nom'])
                 ->setAnneeAcademique($data['annee_academique'])
                 ->setNumero((int)$data['numero'])
                 ->setFiliere($filiere)
                 ->setActif($data['actif'] ?? false);

        $this->em->persist($semestre);
        $this->em->flush();

        return $semestre;
    }

    public function update(Semestre $semestre, array $data): Semestre
    {
        if (!empty($data['nom']))              $semestre->setNom($data['nom']);
        if (!empty($data['annee_academique'])) $semestre->setAnneeAcademique($data['annee_academique']);
        if (!empty($data['numero']))           $semestre->setNumero((int)$data['numero']);
        if (isset($data['actif']))             $semestre->setActif((bool)$data['actif']);

        $this->em->flush();

        return $semestre;
    }

    public function delete(Semestre $semestre): void
    {
        $this->em->remove($semestre);
        $this->em->flush();
    }

    public function serialize(Semestre $s): array
    {
        return [
            'id'               => $s->getId(),
            'nom'              => $s->getNom(),
            'numero'           => $s->getNumero(),
            'annee_academique' => $s->getAnneeAcademique(),
            'actif'            => $s->isActif(),
            'filiere'          => [
                'id'   => $s->getFiliere()?->getId(),
                'nom'  => $s->getFiliere()?->getNom(),
                'code' => $s->getFiliere()?->getCode(),
            ],
        ];
    }
}
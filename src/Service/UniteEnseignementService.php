<?php

namespace App\Service;

use App\Entity\UniteEnseignement;
use App\Entity\Semestre;
use App\Entity\Enseignant;
use Doctrine\ORM\EntityManagerInterface;

class UniteEnseignementService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAll(?string $semestreId, ?string $enseignantId): array
    {
        $criteria = [];

        if ($semestreId) {
            $semestre = $this->em->getRepository(Semestre::class)->find($semestreId);
            if (!$semestre) throw new \Exception('Semestre introuvable');
            $criteria['semestre'] = $semestre;
        }

        if ($enseignantId) {
            $enseignant = $this->em->getRepository(Enseignant::class)->find($enseignantId);
            if (!$enseignant) throw new \Exception('Enseignant introuvable');
            $criteria['enseignant'] = $enseignant;
        }

        return $this->em->getRepository(UniteEnseignement::class)->findBy($criteria);
    }

    public function getById(string $id): UniteEnseignement
    {
        $ue = $this->em->getRepository(UniteEnseignement::class)->find($id);
        if (!$ue) throw new \Exception('UE introuvable');
        return $ue;
    }

    public function create(array $data): UniteEnseignement
    {
        $required = ['nom', 'code', 'coefficient', 'credits_ects', 'semestre_id'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Champ obligatoire manquant : $field");
            }
        }

        $semestre = $this->em->getRepository(Semestre::class)->find($data['semestre_id']);
        if (!$semestre) throw new \Exception('Semestre introuvable');

        $enseignant = null;
        if (!empty($data['enseignant_id'])) {
            $enseignant = $this->em->getRepository(Enseignant::class)->find($data['enseignant_id']);
            if (!$enseignant) throw new \Exception('Enseignant introuvable');
        }

        $ue = new UniteEnseignement();
        $ue->setNom($data['nom'])
           ->setCode(strtoupper($data['code']))
           ->setCoefficient((float) $data['coefficient'])
           ->setCreditsEcts((int) $data['credits_ects'])
           ->setSemestre($semestre)
           ->setEnseignant($enseignant);

        $this->em->persist($ue);
        $this->em->flush();

        return $ue;
    }

    public function update(string $id, array $data): UniteEnseignement
    {
        $ue = $this->getById($id);

        if (!empty($data['nom']))         $ue->setNom($data['nom']);
        if (!empty($data['code']))        $ue->setCode(strtoupper($data['code']));
        if (isset($data['coefficient']))  $ue->setCoefficient((float) $data['coefficient']);
        if (isset($data['credits_ects'])) $ue->setCreditsEcts((int) $data['credits_ects']);

        if (!empty($data['enseignant_id'])) {
            $enseignant = $this->em->getRepository(Enseignant::class)->find($data['enseignant_id']);
            if (!$enseignant) throw new \Exception('Enseignant introuvable');
            $ue->setEnseignant($enseignant);
        }

        $this->em->flush();
        return $ue;
    }

    public function delete(string $id): void
    {
        $ue = $this->getById($id);
        $this->em->remove($ue);
        $this->em->flush();
    }
}
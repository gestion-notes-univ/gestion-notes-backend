<?php

namespace App\Service;

use App\Entity\UniteEnseignement;
use App\Entity\Semestre;
use App\Entity\Enseignant;
use Doctrine\ORM\EntityManagerInterface;

class UniteEnseignementService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAll(array $criteria = [])
    {
        return $this->em->getRepository(UniteEnseignement::class)->findBy($criteria);
    }

    public function getById(string $id): ?UniteEnseignement
    {
        return $this->em->getRepository(UniteEnseignement::class)->find($id);
    }

    public function create(array $data): UniteEnseignement
    {
        if ($this->em->getRepository(UniteEnseignement::class)->findOneBy(['code' => strtoupper($data['code'])])) {
            throw new \Exception('duplicate_code');
        }

        $semestre = $this->em->getRepository(Semestre::class)->find($data['semestre_id']);
        if (!$semestre) throw new \Exception('semestre_not_found');

        $enseignant = null;
        if (!empty($data['enseignant_id'])) {
            $enseignant = $this->em->getRepository(Enseignant::class)->find($data['enseignant_id']);
            if (!$enseignant) throw new \Exception('enseignant_not_found');
        }

        $ue = new UniteEnseignement();
        $ue->setNom($data['nom'])
           ->setCode(strtoupper($data['code']))
           ->setCoefficient((float) $data['coefficient'])
           ->setCreditsEcts((int) $data['credits_ects'])
           ->setNoteMinimum(isset($data['note_minimum']) ? (float) $data['note_minimum'] : 10.0)
           ->setSemestre($semestre)
           ->setEnseignant($enseignant);

        $this->em->persist($ue);
        $this->em->flush();

        return $ue;
    }

    public function update(UniteEnseignement $ue, array $data): UniteEnseignement
    {
        if (!empty($data['nom']))         $ue->setNom($data['nom']);
        if (!empty($data['code']))        $ue->setCode(strtoupper($data['code']));
        if (isset($data['coefficient']))  $ue->setCoefficient((float) $data['coefficient']);
        if (isset($data['credits_ects'])) $ue->setCreditsEcts((int) $data['credits_ects']);
        if (isset($data['note_minimum'])) $ue->setNoteMinimum((float) $data['note_minimum']);

        if (array_key_exists('enseignant_id', $data)) {
            if ($data['enseignant_id'] === null) {
                $ue->setEnseignant(null);
            } else {
                $enseignant = $this->em->getRepository(Enseignant::class)->find($data['enseignant_id']);
                if (!$enseignant) throw new \Exception('enseignant_not_found');
                $ue->setEnseignant($enseignant);
            }
        }

        $this->em->flush();

        return $ue;
    }

    public function delete(UniteEnseignement $ue): void
    {
        $this->em->remove($ue);
        $this->em->flush();
    }
}
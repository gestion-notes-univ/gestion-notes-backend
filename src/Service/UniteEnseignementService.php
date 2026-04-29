<?php

namespace App\Service;

use App\Entity\UniteEnseignement;
use App\Entity\Semestre;
use App\Entity\Enseignant;
use Doctrine\ORM\EntityManagerInterface;

class UniteEnseignementService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Récupère toutes les UEs
     * Filtre optionnel : semestreId et/ou enseignantId
     */
    public function getAll(?string $semestreId = null, ?string $enseignantId = null): array
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

    /**
     * Récupère une UE par son id
     */
    public function getById(string $id): ?UniteEnseignement
    {
        return $this->em->getRepository(UniteEnseignement::class)->find($id);
    }

    /**
     * Crée une nouvelle UE
     * Champs requis : nom, code, coefficient, credits_ects, semestre_id
     */
    public function create(array $data): UniteEnseignement
    {
        $required = ['nom', 'code', 'coefficient', 'credits_ects', 'semestre_id'];
        foreach ($required as $field) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                throw new \Exception("Champ obligatoire manquant : $field");
            }
        }

        if ($this->em->getRepository(UniteEnseignement::class)->findOneBy(['code' => strtoupper($data['code'])])) {
            throw new \Exception('Ce code UE existe déjà');
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
           ->setNoteMinimum(isset($data['note_minimum']) ? (float) $data['note_minimum'] : 10.0)
           ->setSemestre($semestre)
           ->setEnseignant($enseignant);

        $this->em->persist($ue);
        $this->em->flush();

        return $ue;
    }

    /**
     * Met à jour une UE existante
     * Reçoit l'objet UniteEnseignement directement
     */
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
                if (!$enseignant) throw new \Exception('Enseignant introuvable');
                $ue->setEnseignant($enseignant);
            }
        }

        if (!empty($data['semestre_id'])) {
            $semestre = $this->em->getRepository(Semestre::class)->find($data['semestre_id']);
            if (!$semestre) throw new \Exception('Semestre introuvable');
            $ue->setSemestre($semestre);
        }

        $this->em->flush();

        return $ue;
    }

    /**
     * Supprime une UE existante
     */
    public function delete(UniteEnseignement $ue): void
    {
        $this->em->remove($ue);
        $this->em->flush();
    }

    /**
     * Sérialise une UE en tableau JSON
     */
    public function serialize(UniteEnseignement $ue): array
    {
        return [
            'id'           => $ue->getId(),
            'nom'          => $ue->getNom(),
            'code'         => $ue->getCode(),
            'coefficient'  => $ue->getCoefficient(),
            'credits_ects' => $ue->getCreditsEcts(),
            'note_minimum' => $ue->getNoteMinimum(),
            'semestre'     => [
                'id'               => $ue->getSemestre()?->getId(),
                'nom'              => $ue->getSemestre()?->getNom(),
                'annee_academique' => $ue->getSemestre()?->getAnneeAcademique(),
                'numero'           => $ue->getSemestre()?->getNumero(),
            ],
            'enseignant'   => $ue->getEnseignant() ? [
                'id'     => $ue->getEnseignant()->getId(),
                'nom'    => $ue->getEnseignant()->getUtilisateur()->getNom(),
                'prenom' => $ue->getEnseignant()->getUtilisateur()->getPrenom(),
            ] : null,
        ];
    }
}
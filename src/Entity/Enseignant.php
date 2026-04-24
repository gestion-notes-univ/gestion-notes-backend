<?php

namespace App\Entity;

use App\Repository\EnseignantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnseignantRepository::class)]
#[ORM\Table(name: 'enseignants')]
class Enseignant
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\OneToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $utilisateur;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $grade = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $specialite = null;

    #[ORM\OneToMany(mappedBy: 'enseignant', targetEntity: UniteEnseignement::class)]
    private Collection $unitesEnseignement;

    public function __construct()
    {
        $this->unitesEnseignement = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $u): static { $this->utilisateur = $u; return $this; }
    public function getGrade(): ?string { return $this->grade; }
    public function setGrade(?string $g): static { $this->grade = $g; return $this; }
    public function getSpecialite(): ?string { return $this->specialite; }
    public function setSpecialite(?string $s): static { $this->specialite = $s; return $this; }
    public function getUnitesEnseignement(): Collection { return $this->unitesEnseignement; }
}

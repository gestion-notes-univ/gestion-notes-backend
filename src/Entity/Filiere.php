<?php

namespace App\Entity;

use App\Repository\FiliereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FiliereRepository::class)]
#[ORM\Table(name: 'filieres')]
class Filiere
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 20, unique: true)]
    private string $code;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $departement = null;

    #[ORM\OneToMany(mappedBy: 'filiere', targetEntity: Etudiant::class)]
    private Collection $etudiants;

    #[ORM\OneToMany(mappedBy: 'filiere', targetEntity: Semestre::class)]
    private Collection $semestres;

    public function __construct()
    {
        $this->etudiants = new ArrayCollection();
        $this->semestres = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getCode(): string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }
    public function getDepartement(): ?string { return $this->departement; }
    public function setDepartement(?string $d): static { $this->departement = $d; return $this; }
    public function getEtudiants(): Collection { return $this->etudiants; }
    public function getSemestres(): Collection { return $this->semestres; }
}

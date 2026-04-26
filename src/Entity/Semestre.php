<?php

namespace App\Entity;

use App\Repository\SemestreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemestreRepository::class)]
#[ORM\Table(name: 'semestres')]
class Semestre
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Filiere::class, inversedBy: 'semestres')]
    #[ORM\JoinColumn(name: 'filiere_id', referencedColumnName: 'id')]
    private Filiere $filiere;

    #[ORM\Column(length: 50)]
    private string $nom;

    #[ORM\Column(name: 'annee_academique', length: 9)]
    private string $anneeAcademique;

    #[ORM\Column(type: 'integer')]
    #[ORM\CheckConstraint(name: 'check_semestre_numero_range', expression: '(numero BETWEEN 1 AND 8)')]
    private int $numero;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $actif = false;

    #[ORM\OneToMany(mappedBy: 'semestre', targetEntity: UniteEnseignement::class)]
    private Collection $unitesEnseignement;

    public function __construct()
    {
        $this->unitesEnseignement = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getFiliere(): Filiere { return $this->filiere; }
    public function setFiliere(Filiere $f): static { $this->filiere = $f; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $n): static { $this->nom = $n; return $this; }
    public function getAnneeAcademique(): string { return $this->anneeAcademique; }
    public function setAnneeAcademique(string $a): static { $this->anneeAcademique = $a; return $this; }
    public function getNumero(): int { return $this->numero; }
    public function setNumero(int $n): static { $this->numero = $n; return $this; }
    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $a): static { $this->actif = $a; return $this; }
    public function getUnitesEnseignement(): Collection { return $this->unitesEnseignement; }
}

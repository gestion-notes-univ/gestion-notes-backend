<?php

namespace App\Entity;

use App\Repository\EtudiantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtudiantRepository::class)]
#[ORM\Table(name: 'etudiants')]
class Etudiant
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\OneToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $utilisateur;

    #[ORM\ManyToOne(targetEntity: Filiere::class, inversedBy: 'etudiants')]
    #[ORM\JoinColumn(name: 'filiere_id', referencedColumnName: 'id')]
    private Filiere $filiere;

    #[ORM\Column(name: 'numero_etudiant', length: 20, unique: true)]
    private string $numeroEtudiant;

    #[ORM\Column(name: 'annee_inscription', type: 'integer')]
    private int $anneeInscription;

    #[ORM\OneToMany(mappedBy: 'etudiant', targetEntity: Note::class)]
    private Collection $notes;

    #[ORM\OneToMany(mappedBy: 'etudiant', targetEntity: Deliberation::class)]
    private Collection $deliberations;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->deliberations = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $u): static { $this->utilisateur = $u; return $this; }
    public function getFiliere(): Filiere { return $this->filiere; }
    public function setFiliere(Filiere $f): static { $this->filiere = $f; return $this; }
    public function getNumeroEtudiant(): string { return $this->numeroEtudiant; }
    public function setNumeroEtudiant(string $n): static { $this->numeroEtudiant = $n; return $this; }
    public function getAnneeInscription(): int { return $this->anneeInscription; }
    public function setAnneeInscription(int $a): static { $this->anneeInscription = $a; return $this; }
    public function getNotes(): Collection { return $this->notes; }
    public function getDeliberations(): Collection { return $this->deliberations; }
}

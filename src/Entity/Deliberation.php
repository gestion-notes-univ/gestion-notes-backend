<?php
namespace App\Entity;

use App\Repository\DeliberationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliberationRepository::class)]
#[ORM\Table(name: 'deliberations')]
class Deliberation
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Etudiant::class, inversedBy: 'deliberations')]
    #[ORM\JoinColumn(name: 'etudiant_id', referencedColumnName: 'id')]
    private Etudiant $etudiant;

    #[ORM\ManyToOne(targetEntity: Semestre::class)]
    #[ORM\JoinColumn(name: 'semestre_id', referencedColumnName: 'id')]
    private Semestre $semestre;

    #[ORM\Column(name: 'moyenne_generale', type: 'float', nullable: true)]
    private ?float $moyenneGenerale = null;

    #[ORM\Column(name: 'credits_valides', type: 'integer', nullable: true)]
    private ?int $creditsValides = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $decision = null;

    #[ORM\Column(name: 'date_deliberation', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateDeliberation = null;

    public function getId(): ?string { return $this->id; }
    public function getEtudiant(): Etudiant { return $this->etudiant; }
    public function setEtudiant(Etudiant $e): static { $this->etudiant = $e; return $this; }
    public function getSemestre(): Semestre { return $this->semestre; }
    public function setSemestre(Semestre $s): static { $this->semestre = $s; return $this; }
    public function getMoyenneGenerale(): ?float { return $this->moyenneGenerale; }
    public function setMoyenneGenerale(?float $m): static { $this->moyenneGenerale = $m; return $this; }
    public function getCreditsValides(): ?int { return $this->creditsValides; }
    public function setCreditsValides(?int $c): static { $this->creditsValides = $c; return $this; }
    public function getDecision(): ?string { return $this->decision; }
    public function setDecision(?string $d): static { $this->decision = $d; return $this; }
    public function getDateDeliberation(): ?\DateTimeInterface { return $this->dateDeliberation; }
    public function setDateDeliberation(?\DateTimeInterface $d): static { $this->dateDeliberation = $d; return $this; }
}

<?php
namespace App\Entity;

use App\Repository\UniteEnseignementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UniteEnseignementRepository::class)]
#[ORM\Table(name: 'unites_enseignement')]
class UniteEnseignement
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Semestre::class, inversedBy: 'unitesEnseignement')]
    #[ORM\JoinColumn(name: 'semestre_id', referencedColumnName: 'id')]
    private Semestre $semestre;

    #[ORM\ManyToOne(targetEntity: Enseignant::class, inversedBy: 'unitesEnseignement')]
    #[ORM\JoinColumn(name: 'enseignant_id', referencedColumnName: 'id')]
    private Enseignant $enseignant;

    #[ORM\Column(length: 150)]
    private string $nom;

    #[ORM\Column(length: 20)]
    private string $code;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2)]
    private string $coefficient;

    #[ORM\Column(name: 'credits_ects', type: 'integer')]
    private int $creditsEcts;

    #[ORM\Column(name: 'note_minimum', type: 'decimal', precision: 4, scale: 2)]
    private string $noteMinimum;

    #[ORM\OneToMany(mappedBy: 'uniteEnseignement', targetEntity: Note::class)]
    private Collection $notes;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getSemestre(): Semestre { return $this->semestre; }
    public function setSemestre(Semestre $s): static { $this->semestre = $s; return $this; }
    public function getEnseignant(): Enseignant { return $this->enseignant; }
    public function setEnseignant(Enseignant $e): static { $this->enseignant = $e; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $n): static { $this->nom = $n; return $this; }
    public function getCode(): string { return $this->code; }
    public function setCode(string $c): static { $this->code = $c; return $this; }
    public function getCoefficient(): string { return $this->coefficient; }
    public function setCoefficient(string $c): static { $this->coefficient = $c; return $this; }
    public function getCreditsEcts(): int { return $this->creditsEcts; }
    public function setCreditsEcts(int $c): static { $this->creditsEcts = $c; return $this; }
    public function getNoteMinimum(): string { return $this->noteMinimum; }
    public function setNoteMinimum(string $n): static { $this->noteMinimum = $n; return $this; }
    public function getNotes(): Collection { return $this->notes; }
}

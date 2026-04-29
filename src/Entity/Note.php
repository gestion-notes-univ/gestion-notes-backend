<?php
namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: 'notes', uniqueConstraints: [new ORM\UniqueConstraint(name: 'unique_etudiant_ue', columns: ['etudiant_id', 'ue_id'])] )]
class Note
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Etudiant::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'etudiant_id', referencedColumnName: 'id')]
    private Etudiant $etudiant;

    #[ORM\ManyToOne(targetEntity: UniteEnseignement::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'ue_id', referencedColumnName: 'id')]
    private UniteEnseignement $ue;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2, nullable: true)]
    #[ORM\CheckConstraint(name: 'check_note_cc_range', expression: '(note_cc BETWEEN 0 AND 20)')]
    private ?float $noteCc = null;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2, nullable: true)]
    #[ORM\CheckConstraint(name: 'check_note_examen_range', expression: '(note_examen BETWEEN 0 AND 20)')]
    private ?float $noteExamen = null;

    #[ORM\Column(
    name: 'note_finale',
    type: 'decimal',
    precision: 4,
    scale: 2,
    nullable: true,
    insertable: false,
    updatable: false
)]
    private ?float $noteFinale = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $validee = false;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'validee_par', referencedColumnName: 'id')]
    private ?Utilisateur $valideePar = null;

    #[ORM\Column(name: 'date_saisie', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(name: 'date_validation', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\OneToMany(mappedBy: 'note', targetEntity: Reclamation::class)]
    private Collection $reclamations;

    public function __construct()
    {
        $this->reclamations = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getEtudiant(): Etudiant { return $this->etudiant; }
    public function setEtudiant(Etudiant $e): static { $this->etudiant = $e; return $this; }
    public function getUe(): UniteEnseignement { return $this->ue; }
    public function setUe(UniteEnseignement $u): static { $this->ue = $u; return $this; }
    public function getNoteCc(): ?float { return $this->noteCc; }
    public function setNoteCc(?float $v): static { $this->noteCc = $v; return $this; }
    public function getNoteExamen(): ?float { return $this->noteExamen; }
    public function setNoteExamen(?float $v): static { $this->noteExamen = $v; return $this; }
    public function getNoteFinale(): ?float { return $this->noteFinale; }
    public function setNoteFinale(?float $v): static { $this->noteFinale = $v; return $this; }
    public function isValidee(): bool { return $this->validee; }
    public function setValidee(bool $v): static { $this->validee = $v; return $this; }
    public function getValideePar(): ?Utilisateur { return $this->valideePar; }
    public function setValideePar(?Utilisateur $u): static { $this->valideePar = $u; return $this; }
    public function getDateSaisie(): ?\DateTimeInterface { return $this->dateSaisie; }
    public function setDateSaisie(?\DateTimeInterface $d): static { $this->dateSaisie = $d; return $this; }
    public function getDateValidation(): ?\DateTimeInterface { return $this->dateValidation; }
    public function setDateValidation(?\DateTimeInterface $d): static { $this->dateValidation = $d; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $c): static { $this->commentaire = $c; return $this; }
    public function getReclamations(): Collection { return $this->reclamations; }
}

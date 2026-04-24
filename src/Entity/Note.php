<?php
namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: 'notes')]
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
    #[ORM\JoinColumn(name: 'unite_enseignement_id', referencedColumnName: 'id')]
    private UniteEnseignement $uniteEnseignement;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $valeur = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $session = null;

    #[ORM\Column(name: 'date_saisie', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\OneToMany(mappedBy: 'note', targetEntity: Reclamation::class)]
    private Collection $reclamations;

    public function __construct()
    {
        $this->reclamations = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getEtudiant(): Etudiant { return $this->etudiant; }
    public function setEtudiant(Etudiant $e): static { $this->etudiant = $e; return $this; }
    public function getUniteEnseignement(): UniteEnseignement { return $this->uniteEnseignement; }
    public function setUniteEnseignement(UniteEnseignement $u): static { $this->uniteEnseignement = $u; return $this; }
    public function getValeur(): ?float { return $this->valeur; }
    public function setValeur(?float $v): static { $this->valeur = $v; return $this; }
    public function getSession(): ?string { return $this->session; }
    public function setSession(?string $s): static { $this->session = $s; return $this; }
    public function getDateSaisie(): ?\DateTimeInterface { return $this->dateSaisie; }
    public function setDateSaisie(?\DateTimeInterface $d): static { $this->dateSaisie = $d; return $this; }
    public function getReclamations(): Collection { return $this->reclamations; }
}

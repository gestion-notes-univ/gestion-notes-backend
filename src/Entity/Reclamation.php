<?php
namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\Table(name: 'reclamations')]
class Reclamation
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Etudiant::class)]
    #[ORM\JoinColumn(name: 'etudiant_id', referencedColumnName: 'id')]
    private Etudiant $etudiant;

    #[ORM\ManyToOne(targetEntity: Note::class, inversedBy: 'reclamations')]
    #[ORM\JoinColumn(name: 'note_id', referencedColumnName: 'id')]
    private Note $note;

    #[ORM\Column(type: 'text')]
    private string $motif;

    #[ORM\Column(length: 20, options: ['default' => 'en_attente'])]
    private string $statut = 'en_attente';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reponse = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string { return $this->id; }
    public function getEtudiant(): Etudiant { return $this->etudiant; }
    public function setEtudiant(Etudiant $e): static { $this->etudiant = $e; return $this; }
    public function getNote(): Note { return $this->note; }
    public function setNote(Note $n): static { $this->note = $n; return $this; }
    public function getMotif(): string { return $this->motif; }
    public function setMotif(string $m): static { $this->motif = $m; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $s): static { $this->statut = $s; return $this; }
    public function getReponse(): ?string { return $this->reponse; }
    public function setReponse(?string $r): static { $this->reponse = $r; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}

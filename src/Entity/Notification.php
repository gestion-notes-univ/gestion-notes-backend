<?php
namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notifications')]
class Notification
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'destinataire_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $destinataire;

    #[ORM\Column(type: 'string', columnDefinition: "type_notification NOT NULL")]
    private string $typeNotif;

    #[ORM\Column(length: 200)]
    private string $titre;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $lue = false;

    #[ORM\Column(name: 'created_at', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string { return $this->id; }
    public function getDestinataire(): Utilisateur { return $this->destinataire; }
    public function setDestinataire(Utilisateur $u): static { $this->destinataire = $u; return $this; }
    public function getTypeNotif(): string { return $this->typeNotif; }
    public function setTypeNotif(string $type): static { $this->typeNotif = $type; return $this; }
    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $m): static { $this->message = $m; return $this; }
    public function isLue(): bool { return $this->lue; }
    public function setLue(bool $lue): static { $this->lue = $lue; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}

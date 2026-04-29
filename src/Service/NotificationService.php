<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getUserNotifications(Utilisateur $user, ?bool $lue): array
    {
        // ✅ destinataire (pas utilisateur) — propriété $destinataire dans l'entité
        $criteria = ['destinataire' => $user];

        if ($lue !== null) {
            // ✅ lue (pas lu) — propriété $lue dans l'entité
            $criteria['lue'] = $lue;
        }

        return $this->em->getRepository(Notification::class)->findBy(
            $criteria,
            ['createdAt' => 'DESC']
        );
    }

    public function markAsRead(string $id, Utilisateur $user): void
    {
        $notification = $this->findOwned($id, $user);
        // ✅ setLue (pas setLu)
        $notification->setLue(true);
        $this->em->flush();
    }

    public function markAllAsRead(Utilisateur $user): void
    {
        // ✅ n.destinataire (pas n.utilisateur) — propriété dans l'entité
        // ✅ n.lue (pas n.lu)
        $this->em->createQuery('
            UPDATE App\Entity\Notification n
            SET n.lue = true
            WHERE n.destinataire = :user AND n.lue = false
        ')->setParameter('user', $user)->execute();
    }

    public function send(array $data): Notification
    {
        $required = ['destinataire_id', 'titre', 'message', 'type_notif'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Champ obligatoire manquant : $field");
            }
        }

        // Valeurs ENUM type_notification
        $typesValides = ['publication_notes', 'deliberation', 'reclamation', 'general'];
        if (!in_array($data['type_notif'], $typesValides)) {
            throw new \Exception('type_notif invalide. Valeurs : ' . implode(', ', $typesValides));
        }

        $user = $this->em->getRepository(Utilisateur::class)->find($data['destinataire_id']);
        if (!$user) throw new \Exception('Utilisateur introuvable');

        $notification = new Notification();
        // ✅ setDestinataire (pas setUtilisateur)
        $notification->setDestinataire($user)
                     ->setTitre($data['titre'])
                     ->setMessage($data['message'])
                     ->setTypeNotif($data['type_notif'])
                     ->setLue(false);
        // ✅ pas besoin de setCreatedAt — le constructeur fait new \DateTime() automatiquement

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    public function delete(string $id, Utilisateur $user): void
    {
        $notification = $this->findOwned($id, $user);
        $this->em->remove($notification);
        $this->em->flush();
    }

    public function serialize(Notification $n): array
    {
        return [
            'id'          => $n->getId(),
            'titre'       => $n->getTitre(),
            'message'     => $n->getMessage(),
            'type_notif'  => $n->getTypeNotif(),
            // ✅ isLue (pas isLu)
            'lue'         => $n->isLue(),
            'created_at'  => $n->getCreatedAt()?->format('d/m/Y H:i'),
        ];
    }

    private function findOwned(string $id, Utilisateur $user): Notification
    {
        $notification = $this->em->getRepository(Notification::class)->find($id);
        if (!$notification) throw new \Exception('Notification introuvable');

        // ✅ getDestinataire (pas getUtilisateur)
        if ($notification->getDestinataire()->getId() !== $user->getId()) {
            throw new \Exception('Accès refusé');
        }

        return $notification;
    }
}
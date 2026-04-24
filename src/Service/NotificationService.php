<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getUserNotifications(Utilisateur $user, ?bool $lu): array
    {
        $criteria = ['utilisateur' => $user];

        if ($lu !== null) {
            $criteria['lu'] = $lu;
        }

        return $this->em->getRepository(Notification::class)->findBy(
            $criteria,
            ['createdAt' => 'DESC']
        );
    }

    public function markAsRead(string $id, Utilisateur $user): void
    {
        $notification = $this->findOwned($id, $user);
        $notification->setLu(true);
        $this->em->flush();
    }

    public function markAllAsRead(Utilisateur $user): void
    {
        $this->em->createQuery('
            UPDATE App\Entity\Notification n
            SET n.lu = true
            WHERE n.utilisateur = :user AND n.lu = false
        ')->setParameter('user', $user)->execute();
    }

    public function send(array $data): Notification
    {
        if (empty($data['utilisateur_id']) || empty($data['message'])) {
            throw new \Exception('Champs obligatoires : utilisateur_id, message');
        }

        $user = $this->em->getRepository(Utilisateur::class)->find($data['utilisateur_id']);
        if (!$user) throw new \Exception('Utilisateur introuvable');

        $notification = new Notification();
        $notification->setUtilisateur($user)
                     ->setMessage($data['message'])
                     ->setLu(false)
                     ->setCreatedAt(new \DateTimeImmutable());

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

    private function findOwned(string $id, Utilisateur $user): Notification
    {
        $notification = $this->em->getRepository(Notification::class)->find($id);
        if (!$notification) throw new \Exception('Notification introuvable');

        if ($notification->getUtilisateur()->getId() !== $user->getId()) {
            throw new \Exception('Accès refusé');
        }

        return $notification;
    }
}
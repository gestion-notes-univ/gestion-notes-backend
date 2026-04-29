<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(private NotificationService $service) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var Utilisateur $user */
            $user = $this->getUser();

            $lu = $request->query->has('lu')
                ? filter_var($request->query->get('lu'), FILTER_VALIDATE_BOOLEAN)
                : null;

            $notifications = $this->service->getUserNotifications($user, $lu);

            return $this->json([
                'total'         => count($notifications),
                'non_lues'      => count(array_filter($notifications, fn($n) => !$n->isLue())),
                'notifications' => array_map([$this->service, 'serialize'], $notifications),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/lire', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function lire(string $id): JsonResponse
    {
        try {
            $this->service->markAsRead($id, $this->getUser());
            return $this->json(['message' => 'Notification lue']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }
    }

    #[Route('/lire-toutes', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function lireTout(): JsonResponse
    {
        $this->service->markAllAsRead($this->getUser());
        return $this->json(['message' => 'Toutes les notifications lues']);
    }

    #[Route('/envoyer', methods: ['POST'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function envoyer(Request $request): JsonResponse
    {
        try {
            $notification = $this->service->send(json_decode($request->getContent(), true));
            return $this->json(['message' => 'Notification envoyée'], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(string $id): JsonResponse
    {
        try {
            $this->service->delete($id, $this->getUser());
            return $this->json(['message' => 'Notification supprimée']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }
    }

}
<?php

namespace App\Controller;
use App\Entity\Utilisateur;
use App\Service\UtilisateurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
class UtilisateurController extends AbstractController
{
    public function __construct(
        private UtilisateurService $service
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $role = $request->query->get('role');
        $data = $this->service->getAll($role);

        return $this->json(array_map([$this->service, 'serialize'], $data));
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $user = $this->service->getOne($id);

        if (!$user) {
            return $this->json(['error' => 'Introuvable'], 404);
        }

        return $this->json($this->service->serialize($user));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $user = $this->service->create($data);

            return $this->json([
                'message' => 'Créé',
                'data' => $this->service->serialize($user)
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $user = $this->service->getOne($id);
        if (!$user) return $this->json(['error' => 'Introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);

            $user = $this->service->update($user, $data);

            return $this->json([
                'message' => 'Mis à jour',
                'data' => $this->service->serialize($user)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $user = $this->service->getOne($id);
        if (!$user) return $this->json(['error' => 'Introuvable'], 404);

        try {
            $this->service->delete($user, $this->getUser());

            return $this->json(['message' => 'Supprimé']);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
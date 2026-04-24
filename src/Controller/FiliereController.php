<?php

namespace App\Controller;

use App\Service\FiliereService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/filieres')]
class FiliereController extends AbstractController
{
    public function __construct(
        private FiliereService $service
    ) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): JsonResponse
    {
        $data = $this->service->getAll();
        return $this->json(array_map([$this->service, 'serialize'], $data));
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(string $id): JsonResponse
    {
        $f = $this->service->getOne($id);
        if (!$f) return $this->json(['error' => 'Introuvable'], 404);

        return $this->json($this->service->serialize($f));
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $f = $this->service->create($data);

            return $this->json([
                'message' => 'Créé',
                'data' => $this->service->serialize($f)
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        $f = $this->service->getOne($id);
        if (!$f) return $this->json(['error' => 'Introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);
            $f = $this->service->update($f, $data);

            return $this->json([
                'message' => 'Mis à jour',
                'data' => $this->service->serialize($f)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        $f = $this->service->getOne($id);
        if (!$f) return $this->json(['error' => 'Introuvable'], 404);

        $this->service->delete($f);

        return $this->json(['message' => 'Supprimé']);
    }
}
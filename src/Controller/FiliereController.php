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
    public function __construct(private FiliereService $service) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): JsonResponse
    {
        return $this->json(array_map([$this, 'serialize'], $this->service->getAll()));
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(string $id): JsonResponse
    {
        $f = $this->service->getById($id);
        if (!$f) return $this->json(['error' => 'Introuvable'], 404);

        return $this->json($this->serialize($f));
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
                'filiere' => $this->serialize($f)
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $f = $this->service->update($id, $data);

        if (!$f) return $this->json(['error' => 'Introuvable'], 404);

        return $this->json([
            'message' => 'Mis à jour',
            'filiere' => $this->serialize($f)
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        if (!$this->service->delete($id)) {
            return $this->json(['error' => 'Introuvable'], 404);
        }

        return $this->json(['message' => 'Supprimé']);
    }

    private function serialize($f): array
    {
        return [
            'id'          => $f->getId(),
            'nom'         => $f->getNom(),
            'code'        => $f->getCode(),
            'departement' => $f->getDepartement(),
        ];
    }
}
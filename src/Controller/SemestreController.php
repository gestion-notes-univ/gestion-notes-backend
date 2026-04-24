<?php

namespace App\Controller;

use App\Service\SemestreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/semestres')]
class SemestreController extends AbstractController
{
    public function __construct(
        private SemestreService $service
    ) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request): JsonResponse
    {
        try {
            $filiereId = $request->query->get('filiere_id');
            $actif = $request->query->has('actif')
                ? filter_var($request->query->get('actif'), FILTER_VALIDATE_BOOLEAN)
                : null;

            $data = $this->service->getAll($filiereId, $actif);

            return $this->json(array_map([$this->service, 'serialize'], $data));

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(string $id): JsonResponse
    {
        $s = $this->service->getOne($id);
        if (!$s) return $this->json(['error' => 'Introuvable'], 404);

        return $this->json($this->service->serialize($s));
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $s = $this->service->create($data);

            return $this->json([
                'message' => 'Créé',
                'data' => $this->service->serialize($s)
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        $s = $this->service->getOne($id);
        if (!$s) return $this->json(['error' => 'Introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);
            $s = $this->service->update($s, $data);

            return $this->json([
                'message' => 'Mis à jour',
                'data' => $this->service->serialize($s)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        $s = $this->service->getOne($id);
        if (!$s) return $this->json(['error' => 'Introuvable'], 404);

        $this->service->delete($s);

        return $this->json(['message' => 'Supprimé']);
    }
}
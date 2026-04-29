<?php

namespace App\Controller;

use App\Service\UniteEnseignementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ues')]
class UniteEnseignementController extends AbstractController
{
    public function __construct(private UniteEnseignementService $service) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $semestreId   = $request->query->get('semestre_id');
        $enseignantId = $request->query->get('enseignant_id');

        try {
            $ues = $this->service->getAll($semestreId, $enseignantId);
            return $this->json(array_map([$this->service, 'serialize'], $ues));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $ue = $this->service->getById($id);
        if (!$ue) return $this->json(['error' => 'UE introuvable'], 404);

        return $this->json($this->service->serialize($ue));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $ue   = $this->service->create($data);

            return $this->json([
                'message' => 'UE créée avec succès',
                'ue'      => $this->service->serialize($ue),
            ], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $ue = $this->service->getById($id);
        if (!$ue) return $this->json(['error' => 'UE introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);
            $ue   = $this->service->update($ue, $data);

            return $this->json([
                'message' => 'UE mise à jour',
                'ue'      => $this->service->serialize($ue),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $ue = $this->service->getById($id);
        if (!$ue) return $this->json(['error' => 'UE introuvable'], 404);

        try {
            $this->service->delete($ue);
            return $this->json(['message' => 'UE supprimée avec succès']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
<?php

namespace App\Controller;

use App\Service\UniteEnseignementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/ues')]
class UniteEnseignementController extends AbstractController
{
    public function __construct(private UniteEnseignementService $service) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request): JsonResponse
    {
        try {
            $ues = $this->service->getAll(
                $request->query->get('semestre_id'),
                $request->query->get('enseignant_id')
            );

            return $this->json(array_map([$this, 'serialize'], $ues));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(string $id): JsonResponse
    {
        try {
            return $this->json($this->serialize($this->service->getById($id)));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        try {
            $ue = $this->service->create(json_decode($request->getContent(), true));
            return $this->json(['message' => 'UE créée', 'ue' => $this->serialize($ue)], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        try {
            $ue = $this->service->update($id, json_decode($request->getContent(), true));
            return $this->json(['message' => 'UE mise à jour', 'ue' => $this->serialize($ue)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return $this->json(['message' => 'UE supprimée']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    private function serialize($ue): array
    {
        return [
            'id'          => $ue->getId(),
            'nom'         => $ue->getNom(),
            'code'        => $ue->getCode(),
            'coefficient' => $ue->getCoefficient(),
            'credits_ects'=> $ue->getCreditsEcts(),
        ];
    }
}
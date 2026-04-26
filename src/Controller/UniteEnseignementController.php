<?php

namespace App\Controller;

use App\Entity\Semestre;
use App\Entity\Enseignant;
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
        $criteria = [];

        if ($semestreId = $request->query->get('semestre_id')) {
            $semestre = $this->getDoctrine()->getRepository(Semestre::class)->find($semestreId);
            if (!$semestre) return $this->json(['error' => 'Semestre introuvable'], 404);
            $criteria['semestre'] = $semestre;
        }

        if ($enseignantId = $request->query->get('enseignant_id')) {
            $enseignant = $this->getDoctrine()->getRepository(Enseignant::class)->find($enseignantId);
            if (!$enseignant) return $this->json(['error' => 'Enseignant introuvable'], 404);
            $criteria['enseignant'] = $enseignant;
        }

        $ues = $this->service->getAll($criteria);

        return $this->json(array_map([$this, 'serialize'], $ues));
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(string $id): JsonResponse
    {
        $ue = $this->service->getById($id);
        if (!$ue) return $this->json(['error' => 'UE introuvable'], 404);

        return $this->json($this->serialize($ue));
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $ue = $this->service->create($data);
            return $this->json(['ue' => $this->serialize($ue)], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        $ue = $this->service->getById($id);
        if (!$ue) return $this->json(['error' => 'UE introuvable'], 404);

        $data = json_decode($request->getContent(), true);

        try {
            $ue = $this->service->update($ue, $data);
            return $this->json(['ue' => $this->serialize($ue)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        $ue = $this->service->getById($id);
        if (!$ue) return $this->json(['error' => 'UE introuvable'], 404);

        $this->service->delete($ue);

        return $this->json(['message' => 'UE supprimée']);
    }

    private function serialize($ue): array
    {
        return [
            'id' => $ue->getId(),
            'nom' => $ue->getNom(),
            'code' => $ue->getCode(),
            'coefficient' => $ue->getCoefficient(),
            'credits_ects' => $ue->getCreditsEcts(),
            'note_minimum' => $ue->getNoteMinimum(),
        ];
    }
}
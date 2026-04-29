<?php

namespace App\Controller;

use App\Service\SemestreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/semestres')]
class SemestreController extends AbstractController
{
    public function __construct(private SemestreService $service) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $filiereId = $request->query->get('filiere_id');
        $actif     = $request->query->has('actif')
                        ? filter_var($request->query->get('actif'), FILTER_VALIDATE_BOOLEAN)
                        : null;

        try {
            $semestres = $this->service->getAll($filiereId, $actif);
            return $this->json(array_map([$this->service, 'serialize'], $semestres));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $semestre = $this->service->getOne($id);
        if (!$semestre) return $this->json(['error' => 'Semestre introuvable'], 404);

        return $this->json($this->service->serialize($semestre));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data     = json_decode($request->getContent(), true);
            $semestre = $this->service->create($data);

            return $this->json([
                'message'  => 'Semestre créé avec succès',
                'semestre' => $this->service->serialize($semestre),
            ], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $semestre = $this->service->getOne($id);
        if (!$semestre) return $this->json(['error' => 'Semestre introuvable'], 404);

        try {
            $data     = json_decode($request->getContent(), true);
            $semestre = $this->service->update($semestre, $data);

            return $this->json([
                'message'  => 'Semestre mis à jour',
                'semestre' => $this->service->serialize($semestre),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $semestre = $this->service->getOne($id);
        if (!$semestre) return $this->json(['error' => 'Semestre introuvable'], 404);

        try {
            $this->service->delete($semestre);
            return $this->json(['message' => 'Semestre supprimé avec succès']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
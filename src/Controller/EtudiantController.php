<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Service\EtudiantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/etudiants')]
class EtudiantController extends AbstractController
{
    public function __construct(
        private EtudiantService $service
    ) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function index(Request $request): JsonResponse
    {
        try {
            $filiereId = $request->query->get('filiere_id');
            $data = $this->service->getAll($filiereId);

            return $this->json(array_map([$this->service, 'serialize'], $data));

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $etudiant = $this->service->getOne($id);

        if (!$etudiant) {
            return $this->json(['error' => 'Introuvable'], 404);
        }

        return $this->json($this->service->serialize($etudiant));
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $e = $this->service->create($data);

            return $this->json([
                'message' => 'Créé',
                'data' => $this->service->serialize($e)
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function update(string $id, Request $request): JsonResponse
    {
        $etudiant = $this->service->getOne($id);
        if (!$etudiant) return $this->json(['error' => 'Introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);
            $e = $this->service->update($etudiant, $data);

            return $this->json([
                'message' => 'Mis à jour',
                'data' => $this->service->serialize($e)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        $etudiant = $this->service->getOne($id);
        if (!$etudiant) return $this->json(['error' => 'Introuvable'], 404);

        $this->service->delete($etudiant);

        return $this->json(['message' => 'Supprimé']);
    }
}
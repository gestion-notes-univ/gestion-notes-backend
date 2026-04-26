<?php

namespace App\Controller;

use App\Service\EnseignantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/enseignants')]
class EnseignantController extends AbstractController
{
    public function __construct(private EnseignantService $service) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): JsonResponse
    {
        $data = array_map([$this, 'serialize'], $this->service->getAll());
        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(string $id): JsonResponse
    {
        $enseignant = $this->service->getById($id);
        if (!$enseignant) return $this->json(['error' => 'Introuvable'], 404);

        return $this->json($this->serialize($enseignant));
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $enseignant = $this->service->create($data);

            return $this->json([
                'message' => 'Créé',
                'data' => $this->serialize($enseignant)
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
        $enseignant = $this->service->update($id, $data);

        if (!$enseignant) return $this->json(['error' => 'Introuvable'], 404);

        return $this->json([
            'message' => 'Mis à jour',
            'data' => $this->serialize($enseignant)
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

    private function serialize($e): array
    {
        return [
            'id'         => $e->getId(),
            'nom'        => $e->getUtilisateur()->getNom(),
            'prenom'     => $e->getUtilisateur()->getPrenom(),
            'email'      => $e->getUtilisateur()->getEmail(),
            'grade'      => $e->getGrade(),
            'specialite' => $e->getSpecialite(),
        ];
    }
}
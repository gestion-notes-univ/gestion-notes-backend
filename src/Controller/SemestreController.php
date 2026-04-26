<?php

namespace App\Controller;

use App\Entity\Filiere;
use App\Service\SemestreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/semestres')]
class SemestreController extends AbstractController
{
    public function __construct(private SemestreService $service) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request): JsonResponse
    {
        $filters = [];

        if ($filiereId = $request->query->get('filiere_id')) {
            $filiere = $this->getDoctrine()->getRepository(Filiere::class)->find($filiereId);
            if (!$filiere) return $this->json(['error' => 'Filiere introuvable'], 404);
            $filters['filiere'] = $filiere;
        }

        if ($request->query->has('actif')) {
            $filters['actif'] = filter_var($request->query->get('actif'), FILTER_VALIDATE_BOOLEAN);
        }

        return $this->json(array_map([$this, 'serialize'], $this->service->getAll($filters)));
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(string $id): JsonResponse
    {
        $s = $this->service->getById($id);
        if (!$s) return $this->json(['error' => 'Introuvable'], 404);

        return $this->json($this->serialize($s));
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
                'semestre' => $this->serialize($s)
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
        $s = $this->service->update($id, $data);

        if (!$s) return $this->json(['error' => 'Introuvable'], 404);

        return $this->json([
            'message' => 'Mis à jour',
            'semestre' => $this->serialize($s)
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

    private function serialize($s): array
    {
        return [
            'id'               => $s->getId(),
            'nom'              => $s->getNom(),
            'numero'           => $s->getNumero(),
            'annee_academique' => $s->getAnneeAcademique(),
            'actif'            => $s->isActif(),
            'filiere'          => [
                'id'          => $s->getFiliere()?->getId(),
                'nom'         => $s->getFiliere()?->getNom(),
                'code'        => $s->getFiliere()?->getCode(),
                'departement' => $s->getFiliere()?->getDepartement(),
            ],
        ];
    }
}
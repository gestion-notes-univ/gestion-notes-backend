<?php

namespace App\Controller;

use App\Entity\Deliberation;
use App\Entity\Etudiant;
use App\Entity\Semestre;
use App\Service\DeliberationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/deliberations')]
class DeliberationController extends AbstractController
{
    public function __construct(
        private DeliberationService $service,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function index(): JsonResponse
    {
        $data = $this->service->getAll();
        return $this->json(array_map([$this->service, 'serialize'], $data));
    }

    #[Route('/etudiant/{id}', methods: ['GET'])]
    public function byEtudiant(string $id): JsonResponse
    {
        $etudiant = $this->em->getRepository(Etudiant::class)->find($id);
        if (!$etudiant) return $this->json(['error' => 'Introuvable'], 404);

        $data = $this->service->getByEtudiant($etudiant);

        return $this->json(array_map([$this->service, 'serialize'], $data));
    }

    #[Route('/calculer', methods: ['POST'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function calculer(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $etudiant = $this->em->getRepository(Etudiant::class)->find($data['etudiant_id']);
            $semestre = $this->em->getRepository(Semestre::class)->find($data['semestre_id']);

            if (!$etudiant || !$semestre) {
                return $this->json(['error' => 'Données invalides'], 400);
            }

            $delib = $this->service->calculer($etudiant, $semestre);

            return $this->json([
                'message' => 'Délibération calculée',
                'data' => $this->service->serialize($delib)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $d = $this->em->getRepository(Deliberation::class)->find($id);
        if (!$d) return $this->json(['error' => 'Introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);
            $d = $this->service->updateDecision($d, $data);

            return $this->json([
                'message' => 'Mis à jour',
                'data' => $this->service->serialize($d)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Etudiant;
use App\Service\ReclamationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/reclamations')]
class ReclamationController extends AbstractController
{
    public function __construct(
        private ReclamationService $service,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function index(): JsonResponse
    {
        $data = $this->service->getAll();
        return $this->json(array_map([$this->service, 'serialize'], $data));
    }

    #[Route('/mes', methods: ['GET'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function mes(): JsonResponse
    {
        $etudiant = $this->em->getRepository(Etudiant::class)
                             ->findOneBy(['utilisateur' => $this->getUser()]);

        if (!$etudiant) return $this->json(['error' => 'Introuvable'], 404);

        $data = $this->service->getByEtudiant($this->getUser());

        return $this->json(array_map([$this->service, 'serialize'], $data));
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function create(Request $request): JsonResponse
    {
        try {
            $etudiant = $this->em->getRepository(Etudiant::class)
                                 ->findOneBy(['utilisateur' => $this->getUser()]);

            $data = json_decode($request->getContent(), true);

            $r = $this->service->create($data,$this->getUser());

            return $this->json([
                'message' => 'Créée',
                'data' => $this->service->serialize($r)
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/traiter', methods: ['PUT'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function traiter(string $id, Request $request): JsonResponse
    {
        $r = $this->em->getRepository(Reclamation::class)->find($id);
        if (!$r) return $this->json(['error' => 'Introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);
            $r = $this->service->traiter($r, $data,null); // a changer 

            return $this->json([
                'message' => 'Traitée',
                'data' => $this->service->serialize($r)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}

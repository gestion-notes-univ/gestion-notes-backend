<?php

namespace App\Controller;

use App\Service\NoteService;
use App\Entity\Etudiant;
use App\Entity\UniteEnseignement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notes')]
class NoteController extends AbstractController
{
    public function __construct(
        private NoteService $service,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $notes = $this->service->getAllNotes();
        return $this->json(array_map([$this->service, 'serialize'], $notes));
    }

    #[Route('/etudiant/{id}', methods: ['GET'])]
    public function byEtudiant(string $id): JsonResponse
    {
        $etudiant = $this->em->getRepository(Etudiant::class)->find($id);
        if (!$etudiant) return $this->json(['error' => 'Étudiant introuvable'], 404);

        $notes = $this->service->getNotesByEtudiant($etudiant);

        return $this->json([
            'etudiant' => $etudiant->getUtilisateur()->getPrenom() . ' ' . $etudiant->getUtilisateur()->getNom(),
            'notes'    => array_map([$this->service, 'serialize'], $notes),
            'moyenne'  => $this->service->calculMoyenne($notes),
        ]);
    }

    #[Route('/ue/{id}', methods: ['GET'])]
    public function byUe(string $id): JsonResponse
    {
        $ue = $this->em->getRepository(UniteEnseignement::class)->find($id);
        if (!$ue) return $this->json(['error' => 'UE introuvable'], 404);

        $notes = $this->service->getNotesByUe($ue);

        return $this->json([
            'ue'    => $ue->getNom(),
            'code'  => $ue->getCode(),
            'notes' => array_map([$this->service, 'serialize'], $notes),
            'stats' => $this->service->calculStats($notes),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $note = $this->service->createNote($data);

            return $this->json([
                'message' => 'Note créée avec succès',
                'note'    => $this->service->serialize($note),
            ], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $note = $this->service->getOne($id);
        if (!$note) return $this->json(['error' => 'Note introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);
            $note = $this->service->updateNote($note, $data);

            return $this->json([
                'message' => 'Note modifiée avec succès',
                'note'    => $this->service->serialize($note),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/valider', methods: ['PUT'])]
    public function valider(string $id): JsonResponse
    {
        $note = $this->service->getOne($id);
        if (!$note) return $this->json(['error' => 'Note introuvable'], 404);

        try {
            $note = $this->service->validerNote($note, $this->getUser());

            return $this->json([
                'message' => 'Note validée avec succès',
                'note'    => $this->service->serialize($note),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $note = $this->service->getOne($id);
        if (!$note) return $this->json(['error' => 'Note introuvable'], 404);

        try {
            $this->service->deleteNote($note);
            return $this->json(['message' => 'Note supprimée avec succès']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\Note;
use App\Entity\UniteEnseignement;
use App\Service\NoteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notes')]
class NoteController extends AbstractController
{
    public function __construct(
        private NoteService $noteService,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function index(): JsonResponse
    {
        $notes = $this->noteService->getAllNotes();

        return $this->json(array_map([$this->noteService, 'serialize'], $notes));
    }

    #[Route('/etudiant/{id}', methods: ['GET'])]
    public function byEtudiant(string $id): JsonResponse
    {
        $etudiant = $this->em->getRepository(Etudiant::class)->find($id);
        if (!$etudiant) return $this->json(['error' => 'Introuvable'], 404);

        $notes = $this->noteService->getNotesByEtudiant($etudiant);

        return $this->json([
            'notes' => array_map([$this->noteService, 'serialize'], $notes),
            'moyenne' => $this->noteService->calculMoyenne($notes)
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_ENSEIGNANT')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $note = $this->noteService->createNote($data);

            return $this->json([
                'message' => 'Note créée',
                'note' => $this->noteService->serialize($note)
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $note = $this->em->getRepository(Note::class)->find($id);
        if (!$note) return $this->json(['error' => 'Introuvable'], 404);

        try {
            $data = json_decode($request->getContent(), true);
            $note = $this->noteService->updateNote($note, $data);

            return $this->json([
                'message' => 'Modifié',
                'note' => $this->noteService->serialize($note)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $note = $this->em->getRepository(Note::class)->find($id);
        if (!$note) return $this->json(['error' => 'Introuvable'], 404);

        $this->noteService->deleteNote($note);

        return $this->json(['message' => 'Supprimé']);
    }
}
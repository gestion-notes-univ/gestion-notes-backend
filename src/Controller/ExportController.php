<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\Semestre;
use App\Service\ExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/export')]
class ExportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ExportService $exportService
    ) {}

    #[Route('/releve/{etudiantId}/{semestreId}', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function relevePdf(string $etudiantId, string $semestreId): Response
    {
        $etudiant = $this->em->getRepository(Etudiant::class)->find($etudiantId);
        $semestre = $this->em->getRepository(Semestre::class)->find($semestreId);

        if (!$etudiant || !$semestre) {
            return $this->json(['error' => 'Données introuvables'], 404);
        }

        $html = $this->renderView('export/releve.html.twig', [
            'etudiant' => $etudiant,
            'semestre' => $semestre
        ]);

        $pdf = $this->exportService->generateRelevePdf($etudiant, $semestre, $html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Route('/pv/{semestreId}', methods: ['GET'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function pvPdf(string $semestreId): Response
    {
        $semestre = $this->em->getRepository(Semestre::class)->find($semestreId);

        if (!$semestre) {
            return $this->json(['error' => 'Semestre introuvable'], 404);
        }

        $html = $this->renderView('export/pv.html.twig', [
            'semestre' => $semestre
        ]);

        $pdf = $this->exportService->generatePvPdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Route('/notes/{semestreId}/excel', methods: ['GET'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function notesExcel(string $semestreId): StreamedResponse
    {
        $semestre = $this->em->getRepository(Semestre::class)->find($semestreId);

        $notes = $this->exportService->getNotesBySemestre($semestre);

        return new StreamedResponse(function () use ($notes) {
            $handle = fopen('php://output', 'w');

            foreach ($notes as $note) {
                fputcsv($handle, [
                    $note->getValeur()
                ]);
            }

            fclose($handle);
        });
    }
}
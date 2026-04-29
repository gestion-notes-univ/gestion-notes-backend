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

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/releve/{etudiantId}/{semestreId}', methods: ['GET'])]
public function relevePdf(string $etudiantId, string $semestreId): Response
{
    $etudiant = $this->em->getRepository(Etudiant::class)->find($etudiantId);
    $semestre = $this->em->getRepository(Semestre::class)->find($semestreId);

    if (!$etudiant || !$semestre) {
        return $this->json(['error' => 'Données introuvables'], 404);
    }

    // ✅ Récupère les notes et la délibération
    $notes        = $this->exportService->getNotesByEtudiantAndSemestre($etudiant, $semestre);
    $deliberation = $this->exportService->getDeliberationByEtudiantAndSemestre($etudiant, $semestre);
    $moyenne      = $this->exportService->calculerMoyenne($notes);

    $html = $this->renderView('export/releve.html.twig', [
        'etudiant'     => $etudiant,
        'semestre'     => $semestre,
        'notes'        => $notes,
        'deliberation' => $deliberation, // ✅ peut être null — le template gère avec {% if deliberation %}
        'moyenne'      => $moyenne,
        'date_export'  => new \DateTime(),
    ]);

    $pdf = $this->exportService->generateRelevePdf($etudiant, $semestre, $html);

    return new Response($pdf, 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="releve.pdf"',
    ]);
}

    #[Route('/pv/{semestreId}', methods: ['GET'])]
    #[IsGranted('ROLE_SCOLARITE')]
    public function pvPdf(string $semestreId): Response
{
    $semestre = $this->em->getRepository(Semestre::class)->find($semestreId);
    if (!$semestre) return $this->json(['error' => 'Semestre introuvable'], 404);

    $deliberations = $this->exportService->getDeliberations($semestre);

    $html = $this->renderView('export/pv.html.twig', [
        'semestre'      => $semestre,
        'deliberations' => $deliberations,
        'date_export'   => new \DateTime(),
        'nb_admis'      => count(array_filter($deliberations, fn($d) => $d->getDecision() === 'admis')),
        'nb_ajourne'    => count(array_filter($deliberations, fn($d) => $d->getDecision() === 'ajourne')),
        'nb_rattrapage' => count(array_filter($deliberations, fn($d) => $d->getDecision() === 'rattrapage')),
    ]);

    $pdf = $this->exportService->generatePvPdf($html);

    return new Response($pdf, 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="pv.pdf"',
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
        $note->getEtudiant()->getNumeroEtudiant(),
        $note->getEtudiant()->getUtilisateur()->getNom(),
        $note->getUe()->getCode(),
        $note->getUe()->getNom(),
        $note->getNoteCc(),
        $note->getNoteExamen(),
        $note->getNoteFinale(),
    ]);
}

            fclose($handle);
        });
    }
}
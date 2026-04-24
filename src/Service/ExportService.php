<?php

namespace App\Service;

use App\Entity\Etudiant;
use App\Entity\Semestre;
use App\Entity\Deliberation;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;

class ExportService
{
    public function __construct(
        private EntityManagerInterface $em,
        private Pdf $pdf
    ) {}

    public function generateRelevePdf(Etudiant $etudiant, Semestre $semestre, string $html): string
    {
        return $this->pdf->getOutputFromHtml($html, [
            'page-size'    => 'A4',
            'orientation'  => 'Portrait',
            'margin-top'   => '15mm',
            'margin-bottom'=> '15mm',
            'margin-left'  => '15mm',
            'margin-right' => '15mm',
        ]);
    }

    public function generatePvPdf(string $html): string
    {
        return $this->pdf->getOutputFromHtml($html, [
            'page-size'    => 'A4',
            'orientation'  => 'Landscape',
            'margin-top'   => '10mm',
            'margin-bottom'=> '10mm',
            'margin-left'  => '10mm',
            'margin-right' => '10mm',
        ]);
    }

    public function getNotesBySemestre(Semestre $semestre): array
    {
        return $this->em->createQuery('
            SELECT n FROM App\Entity\Note n
            JOIN n.uniteEnseignement ue
            WHERE ue.semestre = :semestre
            ORDER BY n.etudiant ASC, ue.code ASC
        ')->setParameter('semestre', $semestre)->getResult();
    }

    public function getDeliberations(Semestre $semestre): array
    {
        return $this->em->getRepository(Deliberation::class)
                        ->findBy(['semestre' => $semestre]);
    }
}
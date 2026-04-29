<?php

namespace App\Service;

use App\Entity\Note;
use App\Entity\Etudiant;
use App\Entity\UniteEnseignement;
use Doctrine\ORM\EntityManagerInterface;

class NoteService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAllNotes(): array
    {
        return $this->em->getRepository(Note::class)->findAll();
    }

    public function getOne(string $id): ?Note
    {
        return $this->em->getRepository(Note::class)->find($id);
    }

    public function getNotesByEtudiant(Etudiant $etudiant): array
    {
        // relation : etudiant_id en BDD → propriété $etudiant dans l'entité
        return $this->em->getRepository(Note::class)
                        ->findBy(['etudiant' => $etudiant]);
    }

    public function getNotesByUe(UniteEnseignement $ue): array
    {
        // relation : ue_id en BDD → propriété $ue dans l'entité (pas uniteEnseignement)
        return $this->em->getRepository(Note::class)
                        ->findBy(['ue' => $ue]);
    }

    /**
     * Crée une note
     * Corps : {
     *   "etudiant_id": "...",
     *   "ue_id": "...",
     *   "note_cc": 12.5,       ← optionnel
     *   "note_examen": 14.0,   ← optionnel
     *   "commentaire": "..."   ← optionnel
     * }
     */
    public function createNote(array $data): Note
    {
        if (empty($data['etudiant_id']) || empty($data['ue_id'])) {
            throw new \Exception('Champs obligatoires : etudiant_id, ue_id');
        }

        // Validation des valeurs entre 0 et 20
        foreach (['note_cc', 'note_examen'] as $field) {
            if (isset($data[$field]) && ($data[$field] < 0 || $data[$field] > 20)) {
                throw new \Exception("$field doit être entre 0 et 20");
            }
        }

        $etudiant = $this->em->getRepository(Etudiant::class)->find($data['etudiant_id']);
        $ue       = $this->em->getRepository(UniteEnseignement::class)->find($data['ue_id']);

        if (!$etudiant) throw new \Exception('Étudiant introuvable');
        if (!$ue)       throw new \Exception('UE introuvable');

        // Vérifier contrainte UNIQUE(etudiant_id, ue_id) en BDD
        $exist = $this->em->getRepository(Note::class)->findOneBy([
            'etudiant' => $etudiant,
            'ue'       => $ue,  // ← propriété $ue dans l'entité
        ]);
        if ($exist) {
            throw new \Exception('Une note existe déjà pour cet étudiant dans cette UE');
        }

        $note = new Note();
        $note->setEtudiant($etudiant)
             ->setUe($ue)  // ← setUe() selon l'entité
             ->setNoteCc(isset($data['note_cc']) ? (float) $data['note_cc'] : null)
             ->setNoteExamen(isset($data['note_examen']) ? (float) $data['note_examen'] : null)
             ->setCommentaire($data['commentaire'] ?? null)
             ->setDateSaisie(new \DateTime());

        // Calcul note_finale côté PHP (PostgreSQL GENERATED la recalcule aussi)
       

        $this->em->persist($note);
        $this->em->flush();

        return $note;
    }

    /**
     * Met à jour une note existante
     * Corps : { "note_cc": 13.0, "note_examen": 15.0, "commentaire": "..." }
     */
    public function updateNote(Note $note, array $data): Note
    {
        if ($note->isValidee()) {
            throw new \Exception('Impossible de modifier une note déjà validée');
        }

        foreach (['note_cc', 'note_examen'] as $field) {
            if (isset($data[$field]) && ($data[$field] < 0 || $data[$field] > 20)) {
                throw new \Exception("$field doit être entre 0 et 20");
            }
        }

        if (array_key_exists('note_cc', $data)) {
            $note->setNoteCc($data['note_cc'] !== null ? (float) $data['note_cc'] : null);
        }
        if (array_key_exists('note_examen', $data)) {
            $note->setNoteExamen($data['note_examen'] !== null ? (float) $data['note_examen'] : null);
        }
        if (array_key_exists('commentaire', $data)) {
            $note->setCommentaire($data['commentaire']);
        }

        

        $this->em->flush();

        return $note;
    }

    /**
     * Valide une note — réservé à la scolarité
     */
    public function validerNote(Note $note, $validePar): Note
    {
        if ($note->isValidee()) {
            throw new \Exception('Note déjà validée');
        }

        $note->setValidee(true)
             ->setValideePar($validePar)
             ->setDateValidation(new \DateTime());

        $this->em->flush();

        return $note;
    }

    /**
     * Supprime une note
     */
    public function deleteNote(Note $note): void
    {
        if ($note->isValidee()) {
            throw new \Exception('Impossible de supprimer une note validée');
        }

        $this->em->remove($note);
        $this->em->flush();
    }

   
    /**
     * Calcul moyenne pondérée par coefficient
     */
    public function calculMoyenne(array $notes): ?float
    {
        $notes = array_filter($notes, fn($n) => $n->getNoteFinale() !== null);
        if (empty($notes)) return null;

        $total     = 0;
        $coefTotal = 0;

        foreach ($notes as $note) {
            $coef       = $note->getUe()->getCoefficient(); // ← getUe()
            $total     += $note->getNoteFinale() * $coef;
            $coefTotal += $coef;
        }

        return $coefTotal > 0 ? round($total / $coefTotal, 2) : null;
    }

    /**
     * Statistiques d'une UE
     */
    public function calculStats(array $notes): array
    {
        $notes = array_filter($notes, fn($n) => $n->getNoteFinale() !== null);
        if (empty($notes)) return [];

        $valeurs = array_map(fn($n) => $n->getNoteFinale(), $notes);

        return [
            'nombre'        => count($valeurs),
            'moyenne'       => round(array_sum($valeurs) / count($valeurs), 2),
            'min'           => min($valeurs),
            'max'           => max($valeurs),
            'nb_admis'      => count(array_filter($valeurs, fn($v) => $v >= 10)),
            'taux_reussite' => round(
                count(array_filter($valeurs, fn($v) => $v >= 10)) / count($valeurs) * 100, 1
            ) . '%',
        ];
    }

    /**
     * Sérialise une note en tableau JSON
     */
    public function serialize(Note $note): array
    {
        $u = $note->getEtudiant()->getUtilisateur();

        return [
            'id'             => $note->getId(),
            'etudiant'       => $u->getPrenom() . ' ' . $u->getNom(), // ← pas getNomComplet()
            'numero_etudiant'=> $note->getEtudiant()->getNumeroEtudiant(),
            'ue'             => $note->getUe()->getNom(),    // ← getUe()
            'code_ue'        => $note->getUe()->getCode(),
            'coefficient'    => $note->getUe()->getCoefficient(),
            'note_cc'        => $note->getNoteCc(),
            'note_examen'    => $note->getNoteExamen(),
            'note_finale'    => $note->getNoteFinale(),
            'validee'        => $note->isValidee(),
            'commentaire'    => $note->getCommentaire(),
            'date_saisie'    => $note->getDateSaisie()?->format('d/m/Y H:i'),
            'date_validation'=> $note->getDateValidation()?->format('d/m/Y H:i'),
        ];
    }
}
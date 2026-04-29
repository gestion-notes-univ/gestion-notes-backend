<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed-demo-data',
    description: 'Ajoute des donnees de demonstration pour tester le frontend.',
)]
class SeedDemoDataCommand extends Command
{
    public function __construct(private Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $passwordHash = password_hash('password', PASSWORD_BCRYPT);

        $this->connection->beginTransaction();

        try {
            $this->seedFilieres();
            $this->seedUsers($passwordHash);
            $this->seedProfiles();
            $this->seedSemestres();
            $this->seedUes();
            $this->seedNotes();
            $this->seedDeliberations();
            $this->seedReclamations();
            $this->seedNotifications();

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }

        $output->writeln('<info>Donnees demo ajoutees. Mot de passe commun: password</info>');
        $output->writeln('<comment>Comptes utiles: demo.admin@mail.com, demo.scolarite@mail.com, demo.prof@mail.com, demo.etudiant1@mail.com</comment>');

        return Command::SUCCESS;
    }

    private function seedFilieres(): void
    {
        $this->connection->executeStatement("
            INSERT INTO filieres (id, nom, code, departement) VALUES
            ('10000000-0000-0000-0000-000000000001', 'Demo Informatique', 'DINFO', 'Sciences et technologies'),
            ('10000000-0000-0000-0000-000000000002', 'Demo Gestion', 'DGEST', 'Economie et management'),
            ('10000000-0000-0000-0000-000000000003', 'Demo Mathematiques appliquees', 'DMATH', 'Sciences fondamentales')
            ON CONFLICT (code) DO UPDATE SET nom = EXCLUDED.nom, departement = EXCLUDED.departement
        ");
    }

    private function seedUsers(string $passwordHash): void
    {
        $this->connection->executeStatement("
            INSERT INTO utilisateurs (id, nom, prenom, email, password_hash, role, actif, created_at) VALUES
            ('20000000-0000-0000-0000-000000000001', 'Demo', 'Admin', 'demo.admin@mail.com', :hash, 'admin', true, NOW()),
            ('20000000-0000-0000-0000-000000000002', 'Demo', 'Scolarite', 'demo.scolarite@mail.com', :hash, 'scolarite', true, NOW()),
            ('20000000-0000-0000-0000-000000000003', 'Rabe', 'Toky', 'demo.prof@mail.com', :hash, 'enseignant', true, NOW()),
            ('20000000-0000-0000-0000-000000000004', 'Rasoa', 'Miora', 'demo.prof2@mail.com', :hash, 'enseignant', true, NOW()),
            ('20000000-0000-0000-0000-000000000005', 'Rakoto', 'Lova', 'demo.etudiant1@mail.com', :hash, 'etudiant', true, NOW()),
            ('20000000-0000-0000-0000-000000000006', 'Randria', 'Sitraka', 'demo.etudiant2@mail.com', :hash, 'etudiant', true, NOW()),
            ('20000000-0000-0000-0000-000000000007', 'Andrianina', 'Hery', 'demo.etudiant3@mail.com', :hash, 'etudiant', true, NOW()),
            ('20000000-0000-0000-0000-000000000008', 'Ravelona', 'Zo', 'demo.etudiant4@mail.com', :hash, 'etudiant', true, NOW())
            ON CONFLICT (email) DO UPDATE SET password_hash = EXCLUDED.password_hash, role = EXCLUDED.role, actif = true
        ", ['hash' => $passwordHash]);
    }

    private function seedProfiles(): void
    {
        $this->connection->executeStatement("
            INSERT INTO enseignants (id, utilisateur_id, grade, specialite) VALUES
            ('30000000-0000-0000-0000-000000000001', '20000000-0000-0000-0000-000000000003', 'Maitre de conferences', 'Algorithmique'),
            ('30000000-0000-0000-0000-000000000002', '20000000-0000-0000-0000-000000000004', 'Professeur', 'Analyse des donnees')
            ON CONFLICT (utilisateur_id) DO UPDATE SET grade = EXCLUDED.grade, specialite = EXCLUDED.specialite
        ");

        $this->connection->executeStatement("
            INSERT INTO etudiants (id, utilisateur_id, filiere_id, numero_etudiant, annee_inscription) VALUES
            ('40000000-0000-0000-0000-000000000001', '20000000-0000-0000-0000-000000000005', '10000000-0000-0000-0000-000000000001', 'INFO-2024-001', 2024),
            ('40000000-0000-0000-0000-000000000002', '20000000-0000-0000-0000-000000000006', '10000000-0000-0000-0000-000000000001', 'INFO-2024-002', 2024),
            ('40000000-0000-0000-0000-000000000003', '20000000-0000-0000-0000-000000000007', '10000000-0000-0000-0000-000000000002', 'GEST-2024-001', 2024),
            ('40000000-0000-0000-0000-000000000004', '20000000-0000-0000-0000-000000000008', '10000000-0000-0000-0000-000000000003', 'MATH-2024-001', 2024)
            ON CONFLICT (numero_etudiant) DO UPDATE SET filiere_id = EXCLUDED.filiere_id, annee_inscription = EXCLUDED.annee_inscription
        ");
    }

    private function seedSemestres(): void
    {
        $this->connection->executeStatement("
            INSERT INTO semestres (id, filiere_id, nom, annee_academique, numero, actif) VALUES
            ('50000000-0000-0000-0000-000000000001', '10000000-0000-0000-0000-000000000001', 'Semestre 1 INFO', '2024-2025', 1, true),
            ('50000000-0000-0000-0000-000000000002', '10000000-0000-0000-0000-000000000001', 'Semestre 2 INFO', '2024-2025', 2, false),
            ('50000000-0000-0000-0000-000000000003', '10000000-0000-0000-0000-000000000002', 'Semestre 1 GEST', '2024-2025', 1, true),
            ('50000000-0000-0000-0000-000000000004', '10000000-0000-0000-0000-000000000003', 'Semestre 1 MATH', '2024-2025', 1, true)
            ON CONFLICT (id) DO UPDATE SET nom = EXCLUDED.nom, actif = EXCLUDED.actif
        ");
    }

    private function seedUes(): void
    {
        $this->connection->executeStatement("
            INSERT INTO unites_enseignement (id, semestre_id, enseignant_id, code, nom, credits_ects, coefficient, note_minimum) VALUES
            ('60000000-0000-0000-0000-000000000001', '50000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000001', 'DINFO101', 'Algorithmique', 4, 2.0, 10),
            ('60000000-0000-0000-0000-000000000002', '50000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000001', 'DINFO102', 'Programmation web', 4, 2.0, 10),
            ('60000000-0000-0000-0000-000000000003', '50000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000002', 'DINFO103', 'Base de donnees', 3, 1.5, 10),
            ('60000000-0000-0000-0000-000000000004', '50000000-0000-0000-0000-000000000002', '30000000-0000-0000-0000-000000000002', 'DINFO201', 'Genie logiciel', 5, 2.5, 10),
            ('60000000-0000-0000-0000-000000000005', '50000000-0000-0000-0000-000000000003', NULL, 'DGEST101', 'Comptabilite generale', 4, 2.0, 10),
            ('60000000-0000-0000-0000-000000000006', '50000000-0000-0000-0000-000000000004', NULL, 'DMATH101', 'Analyse numerique', 4, 2.0, 10)
            ON CONFLICT (code) DO UPDATE SET nom = EXCLUDED.nom, enseignant_id = EXCLUDED.enseignant_id
        ");
    }

    private function seedNotes(): void
    {
        $this->connection->executeStatement("
            INSERT INTO notes (id, etudiant_id, ue_id, note_cc, note_examen, validee, validee_par, date_saisie, date_validation, commentaire) VALUES
            ('70000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000001', '60000000-0000-0000-0000-000000000001', 14.5, 15.0, true, '20000000-0000-0000-0000-000000000002', NOW(), NOW(), 'Bon travail'),
            ('70000000-0000-0000-0000-000000000002', '40000000-0000-0000-0000-000000000001', '60000000-0000-0000-0000-000000000002', 12.0, 13.5, false, NULL, NOW(), NULL, 'A valider'),
            ('70000000-0000-0000-0000-000000000003', '40000000-0000-0000-0000-000000000001', '60000000-0000-0000-0000-000000000003', 9.0, 11.0, true, '20000000-0000-0000-0000-000000000002', NOW(), NOW(), 'Moyen'),
            ('70000000-0000-0000-0000-000000000004', '40000000-0000-0000-0000-000000000002', '60000000-0000-0000-0000-000000000001', 16.0, 17.0, true, '20000000-0000-0000-0000-000000000002', NOW(), NOW(), 'Tres bien'),
            ('70000000-0000-0000-0000-000000000005', '40000000-0000-0000-0000-000000000002', '60000000-0000-0000-0000-000000000002', 8.0, 9.5, false, NULL, NOW(), NULL, 'Rattrapage possible'),
            ('70000000-0000-0000-0000-000000000006', '40000000-0000-0000-0000-000000000003', '60000000-0000-0000-0000-000000000005', 13.0, 12.5, true, '20000000-0000-0000-0000-000000000002', NOW(), NOW(), 'Correct'),
            ('70000000-0000-0000-0000-000000000007', '40000000-0000-0000-0000-000000000004', '60000000-0000-0000-0000-000000000006', 10.5, 14.0, true, '20000000-0000-0000-0000-000000000002', NOW(), NOW(), 'Progression visible')
            ON CONFLICT (etudiant_id, ue_id) DO UPDATE SET note_cc = EXCLUDED.note_cc, note_examen = EXCLUDED.note_examen, validee = EXCLUDED.validee, commentaire = EXCLUDED.commentaire
        ");
    }

    private function seedDeliberations(): void
    {
        $this->connection->executeStatement("
            INSERT INTO deliberations (id, etudiant_id, semestre_id, moyenne_generale, credits_valides, decision, date_deliberation) VALUES
            ('80000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000001', '50000000-0000-0000-0000-000000000001', 12.77, 11, 'admis', NOW()),
            ('80000000-0000-0000-0000-000000000002', '40000000-0000-0000-0000-000000000002', '50000000-0000-0000-0000-000000000001', 12.18, 4, 'rattrapage', NOW()),
            ('80000000-0000-0000-0000-000000000003', '40000000-0000-0000-0000-000000000003', '50000000-0000-0000-0000-000000000003', 12.75, 4, 'admis', NOW())
            ON CONFLICT (etudiant_id, semestre_id) DO UPDATE SET moyenne_generale = EXCLUDED.moyenne_generale, credits_valides = EXCLUDED.credits_valides, decision = EXCLUDED.decision
        ");
    }

    private function seedReclamations(): void
    {
        $this->connection->executeStatement("
            INSERT INTO reclamations (id, etudiant_id, note_id, motif, statut, traitee_par, created_at) VALUES
            ('90000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000001', '70000000-0000-0000-0000-000000000003', 'Je souhaite une verification de ma copie.', 'en_attente', NULL, NOW()),
            ('90000000-0000-0000-0000-000000000002', '40000000-0000-0000-0000-000000000002', '70000000-0000-0000-0000-000000000005', 'Erreur possible sur la note examen.', 'en_cours', '20000000-0000-0000-0000-000000000002', NOW())
            ON CONFLICT (id) DO UPDATE SET statut = EXCLUDED.statut, motif = EXCLUDED.motif
        ");
    }

    private function seedNotifications(): void
    {
        $this->connection->executeStatement("
            INSERT INTO notifications (id, destinataire_id, type_notif, titre, message, lue, created_at) VALUES
            ('a0000000-0000-0000-0000-000000000001', '20000000-0000-0000-0000-000000000005', 'publication_notes', 'Notes disponibles', 'Les notes du semestre 1 sont disponibles.', false, NOW()),
            ('a0000000-0000-0000-0000-000000000002', '20000000-0000-0000-0000-000000000002', 'reclamation', 'Nouvelle reclamation', 'Une reclamation est en attente de traitement.', false, NOW()),
            ('a0000000-0000-0000-0000-000000000003', '20000000-0000-0000-0000-000000000003', 'general', 'Planning', 'Merci de verifier vos UE assignees.', true, NOW())
            ON CONFLICT (id) DO UPDATE SET titre = EXCLUDED.titre, message = EXCLUDED.message, lue = EXCLUDED.lue
        ");
    }
}

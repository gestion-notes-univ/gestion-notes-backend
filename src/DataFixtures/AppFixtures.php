<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct(private Connection $connection) {}

    public function load(ObjectManager $manager): void
    {
        $conn = $this->connection;

        // ─── FILIERES ────────────────────────────────────────────
        $conn->executeStatement("
            INSERT INTO filieres (id, nom, code, departement) VALUES
            (gen_random_uuid(), 'Informatique',   'INFO', 'Sciences'),
            (gen_random_uuid(), 'Mathématiques',  'MATH', 'Sciences')
        ");

        // ─── UTILISATEURS ────────────────────────────────────────
        $hash = '\$2y\$13\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $conn->executeStatement("
            INSERT INTO utilisateurs (id, nom, prenom, email, password_hash, role) VALUES
            ('11111111-1111-1111-1111-111111111111', 'Rakoto', 'Admin',  'admin@univ.mg',  '$hash', 'admin'),
            ('22222222-2222-2222-2222-222222222222', 'Rabe',   'Jean',   'jean@univ.mg',   '$hash', 'enseignant'),
            ('33333333-3333-3333-3333-333333333333', 'Rasoa',  'Marie',  'marie@univ.mg',  '$hash', 'enseignant'),
            ('44444444-4444-4444-4444-444444444444', 'Andry',  'Paul',   'paul@univ.mg',   '$hash', 'scolarite'),
            ('55555555-5555-5555-5555-555555555555', 'Nirina', 'Etud1',  'etud1@univ.mg',  '$hash', 'etudiant'),
            ('66666666-6666-6666-6666-666666666666', 'Hasina', 'Etud2',  'etud2@univ.mg',  '$hash', 'etudiant'),
            ('77777777-7777-7777-7777-777777777777', 'Tahiry', 'Etud3',  'etud3@univ.mg',  '$hash', 'etudiant')
        ");

        // ─── ENSEIGNANTS ──────────────────────────────────────────
        $conn->executeStatement("
            INSERT INTO enseignants (id, utilisateur_id, grade, specialite) VALUES
            ('aaaa0001-0000-0000-0000-000000000001', '22222222-2222-2222-2222-222222222222', 'Maître de conférences', 'Algorithmique'),
            ('aaaa0001-0000-0000-0000-000000000002', '33333333-3333-3333-3333-333333333333', 'Professeur',            'Analyse')
        ");

        // ─── ETUDIANTS ────────────────────────────────────────────
        $conn->executeStatement("
            INSERT INTO etudiants (id, utilisateur_id, filiere_id, numero_etudiant, annee_inscription)
            SELECT
                gen_random_uuid(),
                u.id,
                f.id,
                'ETU-' || ROW_NUMBER() OVER (),
                2024
            FROM utilisateurs u
            CROSS JOIN LATERAL (SELECT id FROM filieres WHERE code = 'INFO' LIMIT 1) f
            WHERE u.role = 'etudiant'
        ");

        // ─── SEMESTRES ────────────────────────────────────────────
        $conn->executeStatement("
            INSERT INTO semestres (id, filiere_id, nom, annee_academique, numero, actif)
            SELECT 'bbbb0001-0000-0000-0000-000000000001', id, 'Semestre 1', '2024-2025', 1, TRUE
            FROM filieres WHERE code = 'INFO'
        ");

        $conn->executeStatement("
            INSERT INTO semestres (id, filiere_id, nom, annee_academique, numero, actif)
            SELECT 'bbbb0001-0000-0000-0000-000000000002', id, 'Semestre 2', '2024-2025', 2, FALSE
            FROM filieres WHERE code = 'INFO'
        ");

        // ─── UNITES D'ENSEIGNEMENT ────────────────────────────────
        $conn->executeStatement("
            INSERT INTO unites_enseignement (id, semestre_id, enseignant_id, code, nom, credits_ects, coefficient, note_minimum) VALUES
            ('cccc0001-0000-0000-0000-000000000001', 'bbbb0001-0000-0000-0000-000000000001', 'aaaa0001-0000-0000-0000-000000000001', 'INFO101', 'Algorithmique',      4, 2.0, 10.0),
            ('cccc0001-0000-0000-0000-000000000002', 'bbbb0001-0000-0000-0000-000000000001', 'aaaa0001-0000-0000-0000-000000000001', 'INFO102', 'Programmation Web',  3, 1.5, 10.0),
            ('cccc0001-0000-0000-0000-000000000003', 'bbbb0001-0000-0000-0000-000000000001', 'aaaa0001-0000-0000-0000-000000000002', 'MATH101', 'Analyse Numérique',  3, 1.5, 10.0),
            ('cccc0001-0000-0000-0000-000000000004', 'bbbb0001-0000-0000-0000-000000000002', 'aaaa0001-0000-0000-0000-000000000001', 'INFO201', 'Base de données',    4, 2.0, 10.0)
        ");

        // ─── NOTES ────────────────────────────────────────────────
        $conn->executeStatement("
            INSERT INTO notes (id, etudiant_id, ue_id, note_cc, note_examen, validee, validee_par, date_validation)
            SELECT
                gen_random_uuid(),
                e.id,
                ue.id,
                ROUND((12 + random() * 8)::numeric, 2),
                ROUND((10 + random() * 10)::numeric, 2),
                TRUE,
                '11111111-1111-1111-1111-111111111111',
                NOW()
            FROM etudiants e
            CROSS JOIN unites_enseignement ue
            WHERE ue.id IN (
                'cccc0001-0000-0000-0000-000000000001',
                'cccc0001-0000-0000-0000-000000000002',
                'cccc0001-0000-0000-0000-000000000003'
            )
        ");

        // ─── NOTIFICATION DE TEST ─────────────────────────────────
        $conn->executeStatement("
            INSERT INTO notifications (id, destinataire_id, type_notif, titre, message, lue)
            VALUES (
                gen_random_uuid(),
                '55555555-5555-5555-5555-555555555555',
                'publication_notes',
                'Vos notes du S1 sont disponibles',
                'Connectez-vous pour consulter vos résultats du Semestre 1.',
                FALSE
            )
        ");

        echo "\n✅ Fixtures chargées avec succès !\n";
        echo "   - 2 filières\n";
        echo "   - 7 utilisateurs (1 admin, 2 enseignants, 1 scolarité, 3 étudiants)\n";
        echo "   - 4 UE sur 2 semestres\n";
        echo "   - Notes générées aléatoirement\n";
        echo "   - 1 notification de test\n";
    }
}

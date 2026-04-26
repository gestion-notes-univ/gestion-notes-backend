<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260424123404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma complet PostgreSQL — gestion de notes universitaires';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DROP TYPE IF EXISTS role_utilisateur CASCADE");
        $this->addSql("DROP TYPE IF EXISTS resultat_deliberation CASCADE");
        $this->addSql("DROP TYPE IF EXISTS statut_reclamation CASCADE");
        $this->addSql("DROP TYPE IF EXISTS type_notification CASCADE");

        $this->addSql("CREATE TYPE role_utilisateur AS ENUM ('admin','enseignant','scolarite','etudiant')");
        $this->addSql("CREATE TYPE resultat_deliberation AS ENUM ('admis','ajourne','rattrapage','exclus')");
        $this->addSql("CREATE TYPE statut_reclamation AS ENUM ('en_attente','en_cours','resolue','rejetee')");
        $this->addSql("CREATE TYPE type_notification AS ENUM ('publication_notes','deliberation','reclamation','general')");

        $this->addSql("
            CREATE TABLE filieres (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                nom VARCHAR(100) NOT NULL,
                code VARCHAR(20) NOT NULL UNIQUE,
                departement VARCHAR(100)
            )
        ");

        $this->addSql("
            CREATE TABLE utilisateurs (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                email VARCHAR(180) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role role_utilisateur NOT NULL DEFAULT 'etudiant',
                actif BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        $this->addSql("
            CREATE TABLE enseignants (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                utilisateur_id UUID NOT NULL UNIQUE REFERENCES utilisateurs(id) ON DELETE CASCADE,
                grade VARCHAR(50),
                specialite VARCHAR(100)
            )
        ");

        $this->addSql("
            CREATE TABLE etudiants (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                utilisateur_id UUID NOT NULL UNIQUE REFERENCES utilisateurs(id) ON DELETE CASCADE,
                filiere_id UUID NOT NULL REFERENCES filieres(id),
                numero_etudiant VARCHAR(20) NOT NULL UNIQUE,
                annee_inscription INT NOT NULL
            )
        ");

        $this->addSql("
            CREATE TABLE semestres (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                filiere_id UUID NOT NULL REFERENCES filieres(id),
                nom VARCHAR(50) NOT NULL,
                annee_academique VARCHAR(9) NOT NULL,
                numero INT NOT NULL CHECK (numero BETWEEN 1 AND 8),
                actif BOOLEAN DEFAULT FALSE
            )
        ");

        $this->addSql("
            CREATE TABLE unites_enseignement (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                semestre_id UUID NOT NULL REFERENCES semestres(id),
                enseignant_id UUID REFERENCES enseignants(id),
                code VARCHAR(20) NOT NULL UNIQUE,
                nom VARCHAR(150) NOT NULL,
                credits_ects INT NOT NULL DEFAULT 3,
                coefficient NUMERIC(4,2) NOT NULL DEFAULT 1.0,
                note_minimum NUMERIC(4,2) NOT NULL DEFAULT 10.0
            )
        ");

        $this->addSql("
            CREATE TABLE notes (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                etudiant_id UUID NOT NULL REFERENCES etudiants(id),
                ue_id UUID NOT NULL REFERENCES unites_enseignement(id),
                note_cc NUMERIC(4,2) CHECK (note_cc BETWEEN 0 AND 20),
                note_examen NUMERIC(4,2) CHECK (note_examen BETWEEN 0 AND 20),
                note_finale NUMERIC(4,2) GENERATED ALWAYS AS (
                    ROUND((COALESCE(note_cc,0) * 0.4 + COALESCE(note_examen,0) * 0.6)::numeric, 2)
                ) STORED,
                validee BOOLEAN DEFAULT FALSE,
                validee_par UUID REFERENCES utilisateurs(id),
                date_saisie TIMESTAMP DEFAULT NOW(),
                date_validation TIMESTAMP,
                commentaire TEXT,
                UNIQUE(etudiant_id, ue_id)
            )
        ");

        $this->addSql("
            CREATE TABLE deliberations (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                etudiant_id UUID NOT NULL REFERENCES etudiants(id),
                semestre_id UUID NOT NULL REFERENCES semestres(id),
                moyenne_generale NUMERIC(4,2),
                credits_valides INT DEFAULT 0,
                decision resultat_deliberation NOT NULL,
                date_deliberation TIMESTAMP DEFAULT NOW(),
                UNIQUE(etudiant_id, semestre_id)
            )
        ");

        $this->addSql("
            CREATE TABLE reclamations (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                etudiant_id UUID NOT NULL REFERENCES etudiants(id),
                note_id UUID NOT NULL REFERENCES notes(id),
                motif TEXT NOT NULL,
                statut statut_reclamation DEFAULT 'en_attente',
                traitee_par UUID REFERENCES utilisateurs(id),
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        $this->addSql("
            CREATE TABLE notifications (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                destinataire_id UUID NOT NULL REFERENCES utilisateurs(id),
                type_notif type_notification NOT NULL,
                titre VARCHAR(200) NOT NULL,
                message TEXT,
                lue BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Vue relevé de notes
        $this->addSql("
            CREATE VIEW v_releve_notes AS
            SELECT
                e.numero_etudiant,
                u.nom || ' ' || u.prenom AS etudiant,
                f.nom AS filiere,
                s.annee_academique,
                s.nom AS semestre,
                ue.code,
                ue.nom AS matiere,
                ue.credits_ects,
                n.note_cc,
                n.note_examen,
                n.note_finale,
                CASE WHEN n.note_finale >= ue.note_minimum
                    THEN 'Validé' ELSE 'Non validé'
                END AS statut
            FROM notes n
            JOIN etudiants e ON e.id = n.etudiant_id
            JOIN utilisateurs u ON u.id = e.utilisateur_id
            JOIN unites_enseignement ue ON ue.id = n.ue_id
            JOIN semestres s ON s.id = ue.semestre_id
            JOIN filieres f ON f.id = s.filiere_id
            WHERE n.validee = TRUE
        ");

        // Index de performance
        $this->addSql('CREATE INDEX idx_notes_etudiant ON notes(etudiant_id)');
        $this->addSql('CREATE INDEX idx_notes_ue ON notes(ue_id)');
        $this->addSql('CREATE INDEX idx_notifications_dest ON notifications(destinataire_id, lue)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW IF EXISTS v_releve_notes');
        $this->addSql('DROP TABLE IF EXISTS notifications');
        $this->addSql('DROP TABLE IF EXISTS reclamations');
        $this->addSql('DROP TABLE IF EXISTS deliberations');
        $this->addSql('DROP TABLE IF EXISTS notes');
        $this->addSql('DROP TABLE IF EXISTS unites_enseignement');
        $this->addSql('DROP TABLE IF EXISTS semestres');
        $this->addSql('DROP TABLE IF EXISTS etudiants');
        $this->addSql('DROP TABLE IF EXISTS enseignants');
        $this->addSql('DROP TABLE IF EXISTS utilisateurs');
        $this->addSql('DROP TABLE IF EXISTS filieres');
        $this->addSql("DROP TYPE IF EXISTS type_notification");
        $this->addSql("DROP TYPE IF EXISTS statut_reclamation");
        $this->addSql("DROP TYPE IF EXISTS resultat_deliberation");
        $this->addSql("DROP TYPE IF EXISTS role_utilisateur");
    }
}

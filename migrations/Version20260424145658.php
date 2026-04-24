<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424145658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deliberations (id UUID NOT NULL, moyenne_generale DOUBLE PRECISION DEFAULT NULL, credits_valides INT DEFAULT NULL, decision VARCHAR(20) DEFAULT NULL, date_deliberation DATE DEFAULT NULL, etudiant_id UUID DEFAULT NULL, semestre_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6FB7C823DDEAB1A3 ON deliberations (etudiant_id)');
        $this->addSql('CREATE INDEX IDX_6FB7C8235577AFDB ON deliberations (semestre_id)');
        $this->addSql('CREATE TABLE enseignants (id UUID NOT NULL, grade VARCHAR(50) DEFAULT NULL, specialite VARCHAR(100) DEFAULT NULL, utilisateur_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BA5EFB5AFB88E14F ON enseignants (utilisateur_id)');
        $this->addSql('CREATE TABLE etudiants (id UUID NOT NULL, numero_etudiant VARCHAR(20) NOT NULL, annee_inscription INT NOT NULL, utilisateur_id UUID DEFAULT NULL, filiere_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_227C02EBD069E583 ON etudiants (numero_etudiant)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_227C02EBFB88E14F ON etudiants (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_227C02EB180AA129 ON etudiants (filiere_id)');
        $this->addSql('CREATE TABLE filieres (id UUID NOT NULL, nom VARCHAR(100) NOT NULL, code VARCHAR(20) NOT NULL, departement VARCHAR(100) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C97A11577153098 ON filieres (code)');
        $this->addSql('CREATE TABLE notes (id UUID NOT NULL, valeur DOUBLE PRECISION DEFAULT NULL, session VARCHAR(20) DEFAULT NULL, date_saisie TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, etudiant_id UUID DEFAULT NULL, unite_enseignement_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_11BA68CDDEAB1A3 ON notes (etudiant_id)');
        $this->addSql('CREATE INDEX IDX_11BA68C18DEEBA5 ON notes (unite_enseignement_id)');
        $this->addSql('CREATE TABLE notifications (id UUID NOT NULL, message TEXT NOT NULL, lu BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, utilisateur_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6000B0D3FB88E14F ON notifications (utilisateur_id)');
        $this->addSql('CREATE TABLE reclamations (id UUID NOT NULL, motif TEXT NOT NULL, statut VARCHAR(20) DEFAULT \'en_attente\' NOT NULL, reponse TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, etudiant_id UUID DEFAULT NULL, note_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1CAD6B76DDEAB1A3 ON reclamations (etudiant_id)');
        $this->addSql('CREATE INDEX IDX_1CAD6B7626ED0855 ON reclamations (note_id)');
        $this->addSql('CREATE TABLE semestres (id UUID NOT NULL, nom VARCHAR(50) NOT NULL, annee_academique VARCHAR(9) NOT NULL, numero INT NOT NULL, actif BOOLEAN DEFAULT false NOT NULL, filiere_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_D9A85823180AA129 ON semestres (filiere_id)');
        $this->addSql('CREATE TABLE unites_enseignement (id UUID NOT NULL, nom VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, coefficient DOUBLE PRECISION NOT NULL, credits_ects INT NOT NULL, semestre_id UUID DEFAULT NULL, enseignant_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C2E54A665577AFDB ON unites_enseignement (semestre_id)');
        $this->addSql('CREATE INDEX IDX_C2E54A66E455FCC0 ON unites_enseignement (enseignant_id)');
        $this->addSql('CREATE TABLE utilisateurs (id UUID NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) NOT NULL, role role_utilisateur NOT NULL DEFAULT \'etudiant\', actif BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_497B315EE7927C74 ON utilisateurs (email)');
        $this->addSql('ALTER TABLE deliberations ADD CONSTRAINT FK_6FB7C823DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiants (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE deliberations ADD CONSTRAINT FK_6FB7C8235577AFDB FOREIGN KEY (semestre_id) REFERENCES semestres (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE enseignants ADD CONSTRAINT FK_BA5EFB5AFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE etudiants ADD CONSTRAINT FK_227C02EBFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE etudiants ADD CONSTRAINT FK_227C02EB180AA129 FOREIGN KEY (filiere_id) REFERENCES filieres (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CDDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiants (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68C18DEEBA5 FOREIGN KEY (unite_enseignement_id) REFERENCES unites_enseignement (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B76DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiants (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B7626ED0855 FOREIGN KEY (note_id) REFERENCES notes (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE semestres ADD CONSTRAINT FK_D9A85823180AA129 FOREIGN KEY (filiere_id) REFERENCES filieres (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE unites_enseignement ADD CONSTRAINT FK_C2E54A665577AFDB FOREIGN KEY (semestre_id) REFERENCES semestres (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE unites_enseignement ADD CONSTRAINT FK_C2E54A66E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES enseignants (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deliberations DROP CONSTRAINT FK_6FB7C823DDEAB1A3');
        $this->addSql('ALTER TABLE deliberations DROP CONSTRAINT FK_6FB7C8235577AFDB');
        $this->addSql('ALTER TABLE enseignants DROP CONSTRAINT FK_BA5EFB5AFB88E14F');
        $this->addSql('ALTER TABLE etudiants DROP CONSTRAINT FK_227C02EBFB88E14F');
        $this->addSql('ALTER TABLE etudiants DROP CONSTRAINT FK_227C02EB180AA129');
        $this->addSql('ALTER TABLE notes DROP CONSTRAINT FK_11BA68CDDEAB1A3');
        $this->addSql('ALTER TABLE notes DROP CONSTRAINT FK_11BA68C18DEEBA5');
        $this->addSql('ALTER TABLE notifications DROP CONSTRAINT FK_6000B0D3FB88E14F');
        $this->addSql('ALTER TABLE reclamations DROP CONSTRAINT FK_1CAD6B76DDEAB1A3');
        $this->addSql('ALTER TABLE reclamations DROP CONSTRAINT FK_1CAD6B7626ED0855');
        $this->addSql('ALTER TABLE semestres DROP CONSTRAINT FK_D9A85823180AA129');
        $this->addSql('ALTER TABLE unites_enseignement DROP CONSTRAINT FK_C2E54A665577AFDB');
        $this->addSql('ALTER TABLE unites_enseignement DROP CONSTRAINT FK_C2E54A66E455FCC0');
        $this->addSql('DROP TABLE deliberations');
        $this->addSql('DROP TABLE enseignants');
        $this->addSql('DROP TABLE etudiants');
        $this->addSql('DROP TABLE filieres');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE reclamations');
        $this->addSql('DROP TABLE semestres');
        $this->addSql('DROP TABLE unites_enseignement');
        $this->addSql('DROP TABLE utilisateurs');
    }
}

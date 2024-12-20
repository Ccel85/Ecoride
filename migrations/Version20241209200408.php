<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241209200408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0FB88E14F');
        $this->addSql('DROP INDEX IDX_8F91ABF0FB88E14F ON avis');
        $this->addSql('ALTER TABLE avis DROP utilisateur_id, CHANGE avis_texte comments VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810F52E93BA0');
        $this->addSql('DROP INDEX IDX_E9E2810F52E93BA0 ON voiture');
        $this->addSql('ALTER TABLE voiture CHANGE voiture_id_id voiture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810F181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id)');
        $this->addSql('CREATE INDEX IDX_E9E2810F181A8BA ON voiture (voiture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis ADD utilisateur_id INT DEFAULT NULL, CHANGE comments avis_texte VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8F91ABF0FB88E14F ON avis (utilisateur_id)');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810F181A8BA');
        $this->addSql('DROP INDEX IDX_E9E2810F181A8BA ON voiture');
        $this->addSql('ALTER TABLE voiture CHANGE voiture_id voiture_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810F52E93BA0 FOREIGN KEY (voiture_id_id) REFERENCES voiture (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E9E2810F52E93BA0 ON voiture (voiture_id_id)');
    }
}

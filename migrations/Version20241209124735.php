<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241209124735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE utilisateur_covoiturage (utilisateur_id INT NOT NULL, covoiturage_id INT NOT NULL, INDEX IDX_DC21931AFB88E14F (utilisateur_id), INDEX IDX_DC21931A62671590 (covoiturage_id), PRIMARY KEY(utilisateur_id, covoiturage_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE utilisateur_covoiturage ADD CONSTRAINT FK_DC21931AFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_covoiturage ADD CONSTRAINT FK_DC21931A62671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE avis ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0FB88E14F ON avis (utilisateur_id)');
        $this->addSql('ALTER TABLE covoiturage ADD voiture_id INT NOT NULL');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E89181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id)');
        $this->addSql('CREATE INDEX IDX_28C79E89181A8BA ON covoiturage (voiture_id)');
        $this->addSql('ALTER TABLE voiture ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_E9E2810FFB88E14F ON voiture (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur_covoiturage DROP FOREIGN KEY FK_DC21931AFB88E14F');
        $this->addSql('ALTER TABLE utilisateur_covoiturage DROP FOREIGN KEY FK_DC21931A62671590');
        $this->addSql('DROP TABLE utilisateur_covoiturage');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0FB88E14F');
        $this->addSql('DROP INDEX IDX_8F91ABF0FB88E14F ON avis');
        $this->addSql('ALTER TABLE avis DROP utilisateur_id');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E89181A8BA');
        $this->addSql('DROP INDEX IDX_28C79E89181A8BA ON covoiturage');
        $this->addSql('ALTER TABLE covoiturage DROP voiture_id');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810FFB88E14F');
        $this->addSql('DROP INDEX IDX_E9E2810FFB88E14F ON voiture');
        $this->addSql('ALTER TABLE voiture DROP utilisateur_id');
    }
}

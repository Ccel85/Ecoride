<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205213405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis ADD covoiturage_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF062671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF062671590 ON avis (covoiturage_id)');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_8f91abf0fb88e14f TO IDX_8F91ABF0F16F4AC6');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF062671590');
        $this->addSql('DROP INDEX IDX_8F91ABF062671590 ON avis');
        $this->addSql('ALTER TABLE avis DROP covoiturage_id');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_8f91abf0f16f4ac6 TO IDX_8F91ABF0FB88E14F');
    }
}

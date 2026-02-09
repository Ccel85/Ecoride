<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209115252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis RENAME INDEX k_conducteur_id TO IDX_8F91ABF0F16F4AC6');
        $this->addSql('ALTER TABLE avis RENAME INDEX k_passager_id TO IDX_8F91ABF071A51189');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E9E2810FF0CD7A4F ON voiture (immat)');
        $this->addSql('ALTER TABLE voiture RENAME INDEX k_utilisateur_id TO IDX_E9E2810FFB88E14F');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_E9E2810FF0CD7A4F ON voiture');
        $this->addSql('ALTER TABLE voiture RENAME INDEX idx_e9e2810ffb88e14f TO K_utilisateur_id');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_8f91abf0f16f4ac6 TO K_conducteur_id');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_8f91abf071a51189 TO K_passager_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123122424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage ADD is_go TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE is_conducteur is_conducteur TINYINT(1) NOT NULL, CHANGE is_passager is_passager TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur CHANGE is_conducteur is_conducteur TINYINT(1) DEFAULT NULL, CHANGE is_passager is_passager TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE covoiturage DROP is_go');
    }
}

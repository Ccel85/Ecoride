<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250112190037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage CHANGE status status TINYINT(1) NOT NULL, CHANGE conducteur_id conducteur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E89F16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_28C79E89F16F4AC6 ON covoiturage (conducteur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E89F16F4AC6');
        $this->addSql('DROP INDEX IDX_28C79E89F16F4AC6 ON covoiturage');
        $this->addSql('ALTER TABLE covoiturage CHANGE conducteur_id conducteur_id INT NOT NULL, CHANGE status status VARCHAR(255) NOT NULL');
    }
}

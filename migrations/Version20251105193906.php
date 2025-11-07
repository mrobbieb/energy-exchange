<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105193906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__battery_bank AS SELECT id, created_at, updated_at, description FROM battery_bank');
        $this->addSql('DROP TABLE battery_bank');
        $this->addSql('CREATE TABLE battery_bank (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , description VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO battery_bank (id, created_at, updated_at, description) SELECT id, created_at, updated_at, description FROM __temp__battery_bank');
        $this->addSql('DROP TABLE __temp__battery_bank');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE battery_bank ADD COLUMN uuid CHAR(36) NOT NULL');
    }
}

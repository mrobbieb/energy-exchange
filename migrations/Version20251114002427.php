<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114002427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__energy_transaction AS SELECT id, created_at, updated_at, watts FROM energy_transaction');
        $this->addSql('DROP TABLE energy_transaction');
        $this->addSql('CREATE TABLE energy_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, battery_id INTEGER NOT NULL, user_id INTEGER NOT NULL, battery_bank_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , watts INTEGER NOT NULL, CONSTRAINT FK_3807D00B19A19CFC FOREIGN KEY (battery_id) REFERENCES battery (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3807D00BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3807D00BFE25EA57 FOREIGN KEY (battery_bank_id) REFERENCES battery_bank (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO energy_transaction (id, created_at, updated_at, watts) SELECT id, created_at, updated_at, watts FROM __temp__energy_transaction');
        $this->addSql('DROP TABLE __temp__energy_transaction');
        $this->addSql('CREATE INDEX IDX_3807D00B19A19CFC ON energy_transaction (battery_id)');
        $this->addSql('CREATE INDEX IDX_3807D00BA76ED395 ON energy_transaction (user_id)');
        $this->addSql('CREATE INDEX IDX_3807D00BFE25EA57 ON energy_transaction (battery_bank_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__energy_transaction AS SELECT id, created_at, updated_at, watts FROM energy_transaction');
        $this->addSql('DROP TABLE energy_transaction');
        $this->addSql('CREATE TABLE energy_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , watts INTEGER NOT NULL, battery VARCHAR(255) NOT NULL, user VARCHAR(255) NOT NULL, battery_bank VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO energy_transaction (id, created_at, updated_at, watts) SELECT id, created_at, updated_at, watts FROM __temp__energy_transaction');
        $this->addSql('DROP TABLE __temp__energy_transaction');
    }
}

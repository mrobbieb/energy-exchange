<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105185905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE energy_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, battery_id INTEGER NOT NULL, user_id INTEGER NOT NULL, battery_bank_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , watts INTEGER DEFAULT NULL, CONSTRAINT FK_3807D00B19A19CFC FOREIGN KEY (battery_id) REFERENCES battery (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3807D00BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3807D00BFE25EA57 FOREIGN KEY (battery_bank_id) REFERENCES battery_bank (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3807D00B19A19CFC ON energy_transaction (battery_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3807D00BA76ED395 ON energy_transaction (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3807D00BFE25EA57 ON energy_transaction (battery_bank_id)');
        $this->addSql('CREATE TABLE power_source (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, battery_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_4B72AE0C19A19CFC FOREIGN KEY (battery_id) REFERENCES battery (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B72AE0C19A19CFC ON power_source (battery_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__battery AS SELECT id, name, user_id, created_at, battery_bank_id, updated_at FROM battery');
        $this->addSql('DROP TABLE battery');
        $this->addSql('CREATE TABLE battery (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, battery_bank_id INTEGER DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, user_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_D02EF4AEFE25EA57 FOREIGN KEY (battery_bank_id) REFERENCES battery_bank (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO battery (id, name, user_id, created_at, battery_bank_id, updated_at) SELECT id, name, user_id, created_at, battery_bank_id, updated_at FROM __temp__battery');
        $this->addSql('DROP TABLE __temp__battery');
        $this->addSql('CREATE INDEX IDX_D02EF4AEFE25EA57 ON battery (battery_bank_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__exchange AS SELECT id, user_id, battery_id, created_at, updated_at, watts FROM exchange');
        $this->addSql('DROP TABLE exchange');
        $this->addSql('CREATE TABLE exchange (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, battery_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , watts INTEGER NOT NULL)');
        $this->addSql('INSERT INTO exchange (id, user_id, battery_id, created_at, updated_at, watts) SELECT id, user_id, battery_id, created_at, updated_at, watts FROM __temp__exchange');
        $this->addSql('DROP TABLE __temp__exchange');
        $this->addSql('ALTER TABLE user ADD COLUMN email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE energy_transaction');
        $this->addSql('DROP TABLE power_source');
        $this->addSql('CREATE TEMPORARY TABLE __temp__battery AS SELECT id, battery_bank_id, name, user_id, created_at, updated_at FROM battery');
        $this->addSql('DROP TABLE battery');
        $this->addSql('CREATE TABLE battery (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, battery_bank_id INTEGER DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, user_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO battery (id, battery_bank_id, name, user_id, created_at, updated_at) SELECT id, battery_bank_id, name, user_id, created_at, updated_at FROM __temp__battery');
        $this->addSql('DROP TABLE __temp__battery');
        $this->addSql('ALTER TABLE exchange ADD COLUMN uuid CHAR(36) NOT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, uuid, roles, password, credits FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, uuid VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, credits INTEGER NOT NULL)');
        $this->addSql('INSERT INTO "user" (id, uuid, roles, password, credits) SELECT id, uuid, roles, password, credits FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_UUID ON "user" (uuid)');
    }
}

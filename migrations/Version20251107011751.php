<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107011751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__battery AS SELECT id, battery_bank_id, name, user_id, created_at, updated_at FROM battery');
        $this->addSql('DROP TABLE battery');
        $this->addSql('CREATE TABLE battery (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, battery_bank_id INTEGER DEFAULT NULL, user_id INTEGER NOT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_D02EF4AEFE25EA57 FOREIGN KEY (battery_bank_id) REFERENCES battery_bank (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D02EF4AEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO battery (id, battery_bank_id, name, user_id, created_at, updated_at) SELECT id, battery_bank_id, name, user_id, created_at, updated_at FROM __temp__battery');
        $this->addSql('DROP TABLE __temp__battery');
        $this->addSql('CREATE INDEX IDX_D02EF4AEFE25EA57 ON battery (battery_bank_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__battery AS SELECT id, battery_bank_id, user_id, name, created_at, updated_at FROM battery');
        $this->addSql('DROP TABLE battery');
        $this->addSql('CREATE TABLE battery (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, battery_bank_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_D02EF4AEFE25EA57 FOREIGN KEY (battery_bank_id) REFERENCES battery_bank (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO battery (id, battery_bank_id, user_id, name, created_at, updated_at) SELECT id, battery_bank_id, user_id, name, created_at, updated_at FROM __temp__battery');
        $this->addSql('DROP TABLE __temp__battery');
        $this->addSql('CREATE INDEX IDX_D02EF4AEFE25EA57 ON battery (battery_bank_id)');
    }
}

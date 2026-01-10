<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105232039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'RAG metadata support: normalize ai_documents.metadata default and add JSONB expression indexes for routing/filtering';
    }

    public function up(Schema $schema): void
    {
        // Safety: migrations are Postgres-specific
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'This migration is intended for PostgreSQL.'
        );

        /**
         * 1) Normalize metadata default (optional, but helps keep ingestion simple)
         *    - If metadata can be NULL today, this makes it default to {} for new rows.
         *    - We also coalesce existing NULLs to {} (if any).
         */
        $this->addSql("UPDATE ai_documents SET metadata = '{}'::jsonb WHERE metadata IS NULL");
        $this->addSql("ALTER TABLE ai_documents ALTER COLUMN metadata SET DEFAULT '{}'::jsonb");

        /**
         * 2) Expression indexes for fast filtering by metadata keys.
         *    These match queries like:
         *      WHERE (metadata->>'type') = 'policy'
         *    or WHERE (metadata->>'type') IN ('policy','engineering')
         */
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_ai_documents_meta_type ON ai_documents ((metadata->>'type'))");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_ai_documents_meta_product ON ai_documents ((metadata->>'product'))");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_ai_documents_meta_risk ON ai_documents ((metadata->>'risk'))");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_ai_documents_meta_version ON ai_documents ((metadata->>'version'))");

        /**
         * 3) Optional: a partial index that only indexes rows where type is present.
         *    Useful if you have many docs without type during transition.
         *    Keep commented unless you need it.
         */
        // $this->addSql("CREATE INDEX IF NOT EXISTS idx_ai_documents_meta_type_present
        //               ON ai_documents ((metadata->>'type'))
        //               WHERE (metadata ? 'type')");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'This migration is intended for PostgreSQL.'
        );

        // Drop indexes
        $this->addSql("DROP INDEX IF EXISTS idx_ai_documents_meta_type");
        $this->addSql("DROP INDEX IF EXISTS idx_ai_documents_meta_product");
        $this->addSql("DROP INDEX IF EXISTS idx_ai_documents_meta_risk");
        $this->addSql("DROP INDEX IF EXISTS idx_ai_documents_meta_version");

        // Revert default (leave the data cleanup as-is; it's harmless)
        $this->addSql("ALTER TABLE ai_documents ALTER COLUMN metadata DROP DEFAULT");
    }
}
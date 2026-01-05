# Energy Exchange (Symfony)

Energy Exchange is a Symfony-based backend for an energy transaction marketplace. The project is evolving toward an operator-grade platform that supports energy listings, transfers, disputes, and an AI-powered support copilot using Retrieval-Augmented Generation (RAG).

This repository contains the Symfony application, database configuration, and AI/RAG infrastructure used to index and answer questions from policy and support documentation.

---

## What This Project Does

- Provides a **Symfony backend** for an energy exchange platform
- Manages core concepts like listings, transfers, and transaction states
- Indexes Markdown documentation into a **vector store**
- Uses **RAG (retrieval augmented generation)** to answer support questions strictly from source documents
- Returns structured AI responses with citations and source mapping for UI use

---

## Tech Stack

- PHP 8.x
- Symfony Framework
- Doctrine ORM + Migrations
- SQLite (default local app database)
- PostgreSQL + pgvector (AI vector store)
- Symfony AI Platform (OpenAI client)
- Docker / Docker Compose (optional)

---

## Repository Layout

This project follows standard Symfony conventions:

```
.
├── bin/                 # Symfony console
├── config/              # Framework and bundle configuration
├── migrations/          # Doctrine migrations
├── public/              # Web entrypoint (index.php)
├── src/                 # Application source code
│   ├── AI/              # RAG, retrievers, and AI support logic
│   ├── Command/         # Symfony console commands
│   └── Controller/     # HTTP controllers
├── templates/           # Twig templates (if used)
├── tests/               # PHPUnit tests
├── .env                 # Default environment variables
├── compose.yaml         # Docker Compose configuration
└── composer.json        # PHP dependencies
```

---

## Prerequisites

Local development:

- PHP 8.x
- Composer
- (Optional) Symfony CLI
- (Optional) Docker + Docker Compose

If using PostgreSQL for AI storage:

- PostgreSQL 15+
- `pgvector` extension enabled

---

## Installation

```bash
git clone https://github.com/mrobbieb/energy-exchange.git
cd energy-exchange

composer install
cp .env .env.local
```

Start the application:

```bash
symfony serve
# or
php -S 127.0.0.1:8000 -t public
```

---

## Environment Configuration

### Required

```dotenv
OPENAI_API_KEY=sk-...
```

Notes:
- Quotes are optional unless your value contains spaces
- Use `.env.local` for secrets (never commit them)

---

## Databases

### Application Database (SQLite)

SQLite is used by default for local development.

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### AI Vector Store (PostgreSQL + pgvector)

The AI knowledge base is stored separately using PostgreSQL with pgvector.

You must ensure the `vector` extension exists:

```sql
CREATE EXTENSION IF NOT EXISTS vector;
```

---

## AI / RAG Architecture

### Conceptual Separation

1. **Application Database**
   - Users, listings, transfers, disputes

2. **Vector Store**
   - Indexed documentation (policies, support, engineering knowledge)
   - Used only for retrieval and AI context

This separation allows SQLite for the app while using Postgres where vectors are required.

---

## AI Store & Retriever Commands

Key Symfony console commands:

```bash
php bin/console ai:store:setup <store>
php bin/console ai:store:index <store> <path>
php bin/console ai:store:retrieve <retriever> "your question"
php bin/console ai:store:drop <store>
```

Example:

```bash
php bin/console ai:store:setup ai.store.postgres.default
php bin/console app:ai:index-policies
php bin/console ai:store:retrieve policies "How do points work?"
```

---

## AI Support Responses

The AI support endpoint returns structured JSON:

```json
{
  "answer": "...",
  "citations": [
    { "doc": "partial-and-failed-transfers", "section": "Partial Transfers", "score": 0.35 }
  ],
  "sourceMap": [
    { "source": 1, "doc": "partial-and-failed-transfers", "section": "Partial Transfers" }
  ],
  "sourcesUsed": 1
}
```

This allows UIs to render answers with traceable, auditable sources.

---

## Docker (Optional)

If using Docker Compose:

```bash
docker compose up -d
docker compose exec app php bin/console doctrine:migrations:migrate
```

Adjust service names based on `compose.yaml`.

---

## Useful Symfony Commands

```bash
php bin/console
php bin/console cache:clear
php bin/console debug:container <service>
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:validate
php bin/console doctrine:query:sql "SELECT 1"
```

---

## Troubleshooting

### API key errors

- Ensure `OPENAI_API_KEY` is set and visible to PHP

### `could not find driver`

- Verify PDO extensions are enabled (`pdo_pgsql`, `pdo_sqlite`)

### `permission denied to create extension vector`

- Create the extension once using a privileged Postgres role

### Empty retrieval results

- Confirm documents were indexed
- Verify retriever names
- Check that chunk content is stored in metadata

---

## Roadmap

- Engineering knowledge base ingestion
- Multiple retrievers by domain (policy vs engineering vs safety)
- Admin reindex tooling
- Answer audit logging
- Safer, tiered prompts for electrical guidance

---

## License

TBD


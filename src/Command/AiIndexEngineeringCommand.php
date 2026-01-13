<?php

namespace App\Command;

use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Store\StoreInterface;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\Vectorizer;
use Symfony\AI\Store\Indexer;
use Symfony\AI\Store\Document\Loader\InMemoryLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;


#[AsCommand(name: 'ai:index:engineering', description: 'Indexes engineering markdown files into the AI vector store.')]
final class AiIndexEngineeringCommand extends Command
{
    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly StoreInterface $store,
        private readonly string $engineeringDir = __DIR__ . '/../../resources/engineering',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->platform || $this->store) {
            $output->writeln('<error>AI platform or store not configured</error>');
            return Command::FAILURE;
        }
        
        $paths = glob($this->engineeringDir . '/*.md') ?: [];
        if (!$paths) {
            $output->writeln('<error>No engineering .md files found in resources/engineering</error>');
            return Command::FAILURE;
        }

        $docs = [];

        foreach ($paths as $path) {
            //$content = file_get_contents($path) ?: '';
            $content = file_get_contents($path);
            if ($content === false) {
                $output->writeln("<comment>Skipping unreadable file: $path</comment>");
                continue;
            }
            $docTitle = preg_replace('/\.md$/', '', basename($path));

            // v1 chunking: split on markdown headings so retrieval is precise.
            $chunks = $this->chunkMarkdownByHeadings($content);

            foreach ($chunks as $chunkIndex => $chunk) {
                $chunkText = trim((string)($chunk['text'] ?? ''));
                if ($chunkText === '') {
                    continue;
                }

                $chunkPreview = mb_substr(trim(strtok($chunkText, "\n")), 0, 120);

                $docs[] = new TextDocument(
                    id: Uuid::v4(),
                    content: $chunkText,
                    metadata: new Metadata([
                        'type' => 'engineering',
                        'corpus' => 'engineering',
                        'doc_title' => $docTitle,
                        'section' => $chunk['section'] ?? null,
                        'chunk_index' => $chunkIndex,
                        'chunk_preview' => $chunkPreview,
                        'chunk' => $chunkText,   // <-- important for your current DB setup
                        'path' => $path,
                    ]),
                );
            }
        }

        $vectorizer = new Vectorizer($this->platform, 'text-embedding-3-small');
        $indexer = new Indexer(new InMemoryLoader($docs), $vectorizer, $this->store);

        $indexer->index($docs);

        $output->writeln(sprintf('<info>Indexed %d chunks from %d engineering files.</info>', count($docs), count($paths)));

        return Command::SUCCESS;
    }

    /**
     * Returns: array<int, array{section: string|null, text: string}>
     */
    private function chunkMarkdownByHeadings(string $markdown): array
    {
        $markdown = str_replace(["\r\n", "\r"], "\n", $markdown);
        $lines = explode("\n", $markdown);

        $chunks = [];
        $currentSection = null;
        $buffer = [];

        $flush = function () use (&$chunks, &$currentSection, &$buffer): void {
            $text = trim(implode("\n", $buffer));
            if ($text === '') {
                $buffer = [];
                return;
            }
            $chunks[] = [
                'section' => $currentSection,
                'text' => $text,
            ];
            $buffer = [];
        };

        foreach ($lines as $line) {
            if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $m)) {
                $flush();
                $currentSection = trim($m[2]);
                $buffer[] = $line; // include heading in chunk text
                continue;
            }
            $buffer[] = $line;
        }

        $flush();

        if (!$chunks) {
            $text = trim($markdown);
            if ($text !== '') {
                $chunks[] = ['section' => null, 'text' => $text];
            }
        }

        return $chunks;
    }
}

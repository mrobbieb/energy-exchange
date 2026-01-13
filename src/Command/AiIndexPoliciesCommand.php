<?php

namespace App\Command;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\AI\Store\Document\Vectorizer;
use Symfony\AI\Store\Document\Loader\InMemoryLoader;
use Symfony\AI\Store\Indexer;
use Symfony\AI\Store\StoreInterface;
use Symfony\AI\Platform\PlatformInterface;

#[AsCommand(name: 'app:ai:index-policies', description: 'Indexes policy markdown files into the AI vector store.')]
final class AiIndexPoliciesCommand extends Command
{
	public function __construct(
	#[Autowire(service: 'ai.platform.openai')]
        private readonly PlatformInterface $platform,
        private readonly StoreInterface $store,     // should resolve to your postgres store
        private readonly string $policiesDir = __DIR__ . '/../../resources/policies',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->platform || !$this->store) {
            $output->writeln('<error>No policy .md files found in resources/policies</error>');
            return Command::FAILURE;
        }

        $paths = glob($this->policiesDir.'/*.md') ?: [];
        if (!$paths) {
            $output->writeln('<error>No policy .md files found in resources/policies</error>');
            return Command::FAILURE;
        }

        $docs = [];
        foreach ($paths as $path) {
            $content = file_get_contents($path) ?: '';
            $docTitle = preg_replace('/\.md$/', '', basename($path));

            // v1 chunking: split on markdown headings so retrieval is precise.
            $chunks = $this->chunkMarkdownByHeadings($content);

            foreach ($chunks as $chunkIndex => $chunk) {
                $chunkText = $chunk['text'] ?? '';
                $chunkPreview = mb_substr(trim(strtok($chunkText, "\n")), 0, 120);

                $docs[] = new TextDocument(
                    id: Uuid::v4(),
                    content: $chunk['text'],
                    metadata: new Metadata([
                        'type' => 'policy',
                        'corpus' => 'policies',
                        'doc_title' => $docTitle,
                        'section' => $chunk['section'] ?? null,
                        'chunk_index' => $chunkIndex,          // stable identifier
                        'chunk_preview' => $chunkPreview,
                        'chunk' => $chunk['text'],
                        'path' => $path,
                    ]),
                );
            }
        }

        // Embed + index into your postgres store
        $vectorizer = new Vectorizer($this->platform, 'text-embedding-3-small');
        $indexer = new Indexer(new InMemoryLoader($docs), $vectorizer, $this->store);

        $indexer->index($docs);

        $output->writeln(sprintf('<info>Indexed %d chunks from %d files.</info>', count($docs), count($paths)));
        return Command::SUCCESS;
    }

    /**
     * Splits markdown into chunks by headings.
     *
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
            // Heading? (supports # .. ######)
            if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $m)) {
                // flush previous chunk
                $flush();

                // set new section title
                $currentSection = trim($m[2]);

                // include heading line inside the chunk text (helps retrieval)
                $buffer[] = $line;
                continue;
            }

            $buffer[] = $line;
        }

        // last chunk
        $flush();

        // If the doc had no headings at all, ensure we return one chunk
        if (!$chunks) {
            $text = trim($markdown);
            if ($text !== '') {
                $chunks[] = ['section' => null, 'text' => $text];
            }
        }

        return $chunks;
    }
}
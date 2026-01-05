<?php

namespace App\Command;

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
        private readonly PlatformInterface $platform,
        private readonly StoreInterface $store,     // should resolve to your postgres store
        private readonly string $policiesDir = __DIR__ . '/../../resources/policies',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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

            foreach ($chunks as $chunk) {
                $docs[] = new TextDocument(
                    id: Uuid::v4(),
                    content: $chunk['text'],
                    metadata: new Metadata([
                        'doc_title' => $docTitle,
                        'section'   => $chunk['heading'] ?? '',
                        'path'      => $path,
                        'type'      => 'policy',
                        'chunk'     => $chunk['text'],   // ✅ add this line
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
     * Very small, deterministic chunker:
     * - splits by markdown headings (#, ##, ###...)
     * - keeps heading as metadata for citations
     */
    private function chunkMarkdownByHeadings(string $md): array
    {
        $md = str_replace("\r\n", "\n", $md);
        $lines = explode("\n", $md);

        $chunks = [];
        $currentHeading = 'Introduction';
        $buffer = [];

        $flush = function() use (&$chunks, &$buffer, &$currentHeading) {
            $text = trim(implode("\n", $buffer));
            if ($text !== '') {
                $chunks[] = ['heading' => $currentHeading, 'text' => $text];
            }
            $buffer = [];
        };

        foreach ($lines as $line) {
            if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $m)) {
                $flush();
                $currentHeading = trim($m[2]);
                $buffer[] = $line; // keep heading line inside chunk text
                continue;
            }
            $buffer[] = $line;
        }

        $flush();
        return $chunks;
    }
}

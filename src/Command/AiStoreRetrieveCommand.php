<?php

namespace App\Command;

use Symfony\AI\Store\Retriever;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ai:store:retrieve',
    description: 'Retrieve documents from the vector store and optionally filter by metadata.'
)]
final class AiStoreRetrieveCommand extends Command
{
    public function __construct(
        private readonly RetrieverInterface $retriever,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'Search query')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Max results', '5')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Filter by metadata.type (policy|engineering|safety)')
            ->addOption('corpus', null, InputOption::VALUE_OPTIONAL, 'Filter by metadata.corpus (policies|engineering|safety)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query  = (string) $input->getArgument('query');
        $limit  = max(1, (int) $input->getOption('limit'));
        $type   = $input->getOption('type');
        $corpus = $input->getOption('corpus');

        // Options passed to the store query. Some adapters support 'filters' natively.
        $options = ['limit' => $limit];
        $filters = [];

        if (is_string($type) && $type !== '') {
            $filters['type'] = $type;
        }
        if (is_string($corpus) && $corpus !== '') {
            $filters['corpus'] = $corpus;
        }

        if ($filters) {
            $options['filters'] = $filters;
        }

        $output->writeln('');
        $output->writeln(sprintf('Searching for: "%s"', $query));
        if ($filters) {
            $output->writeln('Filters: ' . json_encode($filters));
        }
        $output->writeln('');

        $results = $this->retriever->retrieve($query, $options);

        if (!$results) {
            $output->writeln('<comment>No results.</comment>');
            return Command::SUCCESS;
        }

        foreach ($results as $i => $doc) {
            // These property names may differ slightly depending on your doc class.
            // Most retrievers return objects with id/score/content/metadata.
            $id = $doc->id ?? $doc->getId() ?? '(unknown)';
            $score = $doc->score ?? $doc->getScore() ?? null;

            $meta = $doc->metadata ?? $doc->getMetadata() ?? [];
            if ($meta instanceof \Traversable) {
                $meta = iterator_to_array($meta);
            }

            $output->writeln(sprintf('Result #%d', $i + 1));
            $output->writeln(str_repeat('-', 24));
            $output->writeln('ID:    ' . $id);
            if ($score !== null) {
                $output->writeln('Score: ' . $score);
            }

            $output->writeln('type:        ' . ($meta['type'] ?? '(missing)'));
            $output->writeln('corpus:      ' . ($meta['corpus'] ?? '(missing)'));
            $output->writeln('doc_title:   ' . ($meta['doc_title'] ?? '(missing)'));
            $output->writeln('section:     ' . ($meta['section'] ?? '(missing)'));
            $output->writeln('chunk_index: ' . (isset($meta['chunk_index']) ? (string)$meta['chunk_index'] : '(missing)'));
            $output->writeln('preview:     ' . ($meta['chunk_preview'] ?? '(missing)'));
            $output->writeln('');
        }

        $output->writeln(sprintf('<info>Found %d result(s).</info>', count($results)));

        return Command::SUCCESS;
    }
}
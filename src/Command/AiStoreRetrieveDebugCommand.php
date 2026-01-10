<?php

namespace App\Command;

use Symfony\AI\Store\RetrieverInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ai:store:retrieve:debug',
    description: 'Debug retrieval with optional metadata filtering'
)]
final class AiStoreRetrieveDebugCommand extends Command
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
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Max results', 5)
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'metadata.type (policy|engineering|safety)')
            ->addOption('corpus', null, InputOption::VALUE_OPTIONAL, 'metadata.corpus');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query  = (string) $input->getArgument('query');
        $limit  = (int) $input->getOption('limit');
        $type   = $input->getOption('type');
        $corpus = $input->getOption('corpus');

        $rawIterable = $this->retriever->retrieve($query, [
            'limit' => max(20, $limit * 4),
        ]);

        $filtered = [];
        foreach ($rawIterable as $doc) {
            $meta = $doc->metadata ?? [];

            if ($type && (($meta['type'] ?? null) !== $type)) {
                continue;
            }
            if ($corpus && (($meta['corpus'] ?? null) !== $corpus)) {
                continue;
            }

            $filtered[] = $doc;
        }

        // Re-sort by similarity score (lower is better)
        usort($filtered, fn($a, $b) => $a->score <=> $b->score);

        $results = array_slice($filtered, 0, $limit);

        if (!$results) {
            $output->writeln('<comment>No results after filtering.</comment>');
            return Command::SUCCESS;
        }

        foreach ($results as $i => $doc) {
            $meta = $doc->metadata ?? [];

            $output->writeln('');
            $output->writeln(sprintf('Result #%d', $i + 1));
            $output->writeln(str_repeat('-', 30));
            $output->writeln('ID:    ' . $doc->id);
            $output->writeln('Score: ' . $doc->score);
            $output->writeln('type:        ' . ($meta['type'] ?? '(missing)'));
            $output->writeln('corpus:      ' . ($meta['corpus'] ?? '(missing)'));
            $output->writeln('doc_title:   ' . ($meta['doc_title'] ?? '(missing)'));
            $output->writeln('section:     ' . ($meta['section'] ?? '(missing)'));
            $output->writeln('chunk_index: ' . ($meta['chunk_index'] ?? '(missing)'));
            $output->writeln('preview:     ' . ($meta['chunk_preview'] ?? '(missing)'));
        }

        $output->writeln(sprintf("\n<info>Returned %d result(s).</info>", count($results)));

        return Command::SUCCESS;
    }
}

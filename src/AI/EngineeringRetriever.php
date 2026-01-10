<?php

namespace App\AI;

use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Store\Retriever;
use Symfony\AI\Store\StoreInterface;
use Symfony\AI\Store\Document\Vectorizer;

final class EngineeringRetriever
{
    private Retriever $retriever;

    public function __construct(
        PlatformInterface $platform,
        StoreInterface $store,
    ) {
        $vectorizer = new Vectorizer($platform, 'text-embedding-3-small');
        $this->retriever = new Retriever($vectorizer, $store);
    }

    public function retrieveEngineering(string $question, int $k = 6): iterable
    {
        return $this->retriever->retrieve($question, [
            'limit' => $k,
            'filters' => [
                'type' => 'engineering',
                'corpus' => 'engineering',
            ],
        ]);
    }
}

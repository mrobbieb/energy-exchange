<?php

namespace App\AI\Rag;

use Symfony\AI\Store\Retriever;

final class FilteredRetriever
{
    public function __construct(
        private readonly Retriever $retriever,
    ) {}

    /**
     * @return array<int, mixed>  // adapt to your document wrapper / DTO
     */
    public function retrieveByTypes(string $query, array $types, int $limit): array
    {
        // The options shape depends on your store bridge.
        // Common pattern: ['limit' => X, 'filters' => [...]]
        // Symfony AI docs: retriever passes options to store query (e.g. limit, filters). :contentReference[oaicite:2]{index=2}
        $options = [
            'limit' => $limit,
            'filters' => [
                'type' => $types, // if your store interprets "IN" when value is array
            ],
        ];

        return $this->retriever->retrieve($query, $options);
    }

    /**
     * If your store doesn’t support array IN filters, fall back:
     * retrieve a larger K with no filter, then filter in PHP by metadata['type'].
     */
}

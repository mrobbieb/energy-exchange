<?php

namespace App\AI\Rag;

final class RouteResult
{
    /**
     * @param string[] $signals
     */
    public function __construct(
        public readonly Domain $domain,
        public readonly array $signals = [],
        public readonly float $confidence = 0.7,
    ) {}
}

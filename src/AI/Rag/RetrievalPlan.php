<?php

namespace App\AI\Rag;

final class RetrievalPlan
{
    /**
     * @param array{types: string[], retrieveK: int, provideN: int} $plan
     */
    public function forDomain(Domain $domain): array
    {
        return match ($domain) {
            Domain::POLICY => ['types' => ['policy'], 'retrieveK' => 6,  'provideN' => 4],
            Domain::ENGINEERING => ['types' => ['engineering'], 'retrieveK' => 8, 'provideN' => 4],
            Domain::SAFETY => ['types' => ['safety'], 'retrieveK' => 8, 'provideN' => 4],
            Domain::HYBRID => ['types' => ['policy','engineering'], 'retrieveK' => 10, 'provideN' => 6],
        };
    }
}

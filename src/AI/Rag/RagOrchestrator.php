<?php

namespace App\AI\Rag;

use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
// Whatever your agent interface is (Symfony AI Agent or your wrapper)

final class RagOrchestrator
{
    public function __construct(
        private readonly QuestionRouter $router,
        private readonly RetrievalPlan $plan,
        private readonly FilteredRetriever $retriever,
        private readonly PromptBuilder $promptBuilder,
        private readonly RagResponseComposer $composer, // your existing citations/sourceMap logic
        private readonly object $agent, // replace with your concrete agent type
    ) {}

    public function answer(string $question): array
    {
        $route = $this->router->route($question);
        $plan = $this->plan->forDomain($route->domain);

        $docs = $this->retrieveForDomain($route->domain, $question, $plan['types'], $plan['retrieveK']);

        // Sort by similarity score (you said lower = better)
        usort($docs, fn($a, $b) => ($a->score <=> $b->score)); // adjust to your doc shape

        $provided = array_slice($docs, 0, $plan['provideN']);

        $system = $this->promptBuilder->systemPrompt($route->domain);

        // Build messages
        $messages = new MessageBag(
            Message::forSystem($system),
            // include sources as text in the prompt or via your toolchain pattern
            Message::ofUser($this->composer->buildUserPromptWithSources($question, $provided))
        );

        $result = $this->agent->call($messages);

        return $this->composer->compose($result, $provided, [
            'domain' => $route->domain->value,
            'signals' => $route->signals,
            'confidence' => $route->confidence,
        ]);
    }

    private function retrieveForDomain(Domain $domain, string $q, array $types, int $k): array
    {
        if ($domain === Domain::HYBRID && count($types) > 1) {
            // safest: do two queries and merge
            $half = (int) ceil($k / 2);
            $policy = $this->retriever->retrieveByTypes($q, ['policy'], $half);
            $eng    = $this->retriever->retrieveByTypes($q, ['engineering'], $half);
            return array_merge($policy, $eng);
        }

        return $this->retriever->retrieveByTypes($q, $types, $k);
    }
}

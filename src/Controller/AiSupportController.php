<?php

namespace App\Controller;

use App\AI\PoliciesRag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\AI\EngineeringRag;
use App\AI\QuestionRouter;

final class AiSupportController extends AbstractController
{
    public function __construct(
        private readonly PoliciesRag $rag,
        private readonly EngineeringRag $engineeringRag,
        private readonly QuestionRouter $router,
    ) {}

    #[Route('/ai/support', name: 'ai_support', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?: [];
        $question = trim((string)($payload['question'] ?? ''));

        if ($question === '') {
            return $this->json(['error' => 'Missing question'], 400);
        }

        $route = $this->router->route($question);

        $out = match ($route) {
            'engineering' => $this->engineeringRag->answer($question, 6),
            default => $this->rag->answer($question, 6),
        };

        return $this->json([
            'domain' => $route,
            'answer' => $out['answer'],
            'citations' => $out['citations'],
            'sourceMap' => $out['sourceMap'] ?? [],
            'sourcesUsed' => $out['sourcesUsed'],
        ]);
    }
}

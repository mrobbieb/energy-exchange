<?php

namespace App\Controller;

use App\AI\PoliciesRag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AiSupportController extends AbstractController
{
    public function __construct(
        private readonly PoliciesRag $rag,
    ) {}

    #[Route('/ai/support', name: 'ai_support', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?: [];
        $question = trim((string)($payload['question'] ?? ''));

        if ($question === '') {
            return $this->json(['error' => 'Missing question'], 400);
        }

        $out = $this->rag->answer($question, 6);

        // return $this->json([
        //     'answer' => $out['answer'],
        //     'citations' => $out['citations'],
        //     'sourcesUsed' => $out['sourcesUsed'],
        // ]);
        return $this->json([
            'answer' => $out['answer'],
            'citations' => $out['citations'],
            'sourceMap' => $out['sourceMap'] ?? [],
            'sourcesUsed' => $out['sourcesUsed'],
        ]);
    }
}

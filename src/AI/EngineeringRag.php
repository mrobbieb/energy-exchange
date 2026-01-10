<?php

namespace App\AI;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\Document\Metadata;

final class EngineeringRag
{
    public function __construct(
        private readonly EngineeringRetriever $engineeringRetriever,
        private readonly AgentInterface $agent,
    ) {}

    private function getVectorDocProp(VectorDocument $obj, string $name): mixed
    {
        $r = new \ReflectionClass($obj);
        if (!$r->hasProperty($name)) return null;
        $p = $r->getProperty($name);
        return $p->getValue($obj);
    }

    public function answer(string $question, int $k = 6): array
    {
        $hits = $this->engineeringRetriever->retrieveEngineering($question, $k);

        $candidates = [];
        foreach ($hits as $hit) {
            if (!$hit instanceof VectorDocument) continue;

            /** @var Metadata|null $metadata */
            $metadata = $this->getVectorDocProp($hit, 'metadata');
            $score    = $this->getVectorDocProp($hit, 'score'); // ?float

            $docTitle = $metadata ? ($metadata['doc_title'] ?? 'Engineering') : 'Engineering';
            $section  = $metadata ? ($metadata['section'] ?? '') : '';

            // $content =
            //     $this->getVectorDocProp($hit, 'content')
            //     ?? $this->getVectorDocProp($hit, 'text')
            //     ?? '';
            $content = $this->getVectorDocProp($hit, 'content')
                ?? $this->getVectorDocProp($hit, 'text')
                ?? ($metadata ? ($metadata['chunk'] ?? '') : '');

            $content = is_string($content) ? $content : '';

            if (trim($content) === '') continue;

            $candidates[] = [
                'doc' => (string)$docTitle,
                'section' => (string)$section,
                'score' => is_float($score) ? $score : null,
                'content' => $content,
            ];
        }

        if (!$candidates) {
            return [
                'answer' => "I don’t have enough information in the current engineering knowledge base to answer that yet.",
                'citations' => [],
                'sourceMap' => [],
                'sourcesUsed' => 0,
            ];
        }

        usort($candidates, function ($a, $b) {
            $sa = $a['score']; $sb = $b['score'];
            if ($sa === null && $sb === null) return 0;
            if ($sa === null) return 1;
            if ($sb === null) return -1;
            return $sa <=> $sb;
        });

        $provided = array_slice($candidates, 0, 4);

        $sourcesText = '';
        foreach ($provided as $idx => $src) {
            $n = $idx + 1;
            $sourcesText .= "\n\n[Source {$n}: {$src['doc']} — {$src['section']}]\n";
            $sourcesText .= $src['content'];
        }

        $messages = new MessageBag(
            Message::forSystem(
                "You are the Energy Exchange Engineering Support Copilot.\n".
                "Rules:\n".
                "1) Use ONLY the provided Sources.\n".
                "2) If the Sources do not fully answer the question, answer with what they DO say, then state what’s missing.\n".
                "3) Do not invent specifications, voltages, wiring sizes, or safety claims beyond the Sources.\n".
                "4) Add citations like (Source 1) after claims.\n".
                "5) Use only the Source numbers exactly as provided in the Sources block; do not invent Source numbers.\n"
            ),
            Message::ofUser("Question:\n{$question}\n\nSources:\n{$sourcesText}\n")
        );

        $result = $this->agent->call($messages);

        $citations = array_map(fn($src) => [
            'doc' => $src['doc'],
            'section' => $src['section'],
            'score' => $src['score'],
        ], $provided);

        $sourceMap = array_map(fn($c, $idx) => [
            'source' => $idx + 1,
            'doc' => $c['doc'],
            'section' => $c['section'],
            'chunk_index' => $c['chunk_index'],
        ], $citations, array_keys($citations));

        return [
            'answer' => $result->getContent(),
            'citations' => $citations,
            'sourceMap' => $sourceMap,
            'sourcesUsed' => count($citations),
        ];
    }
}

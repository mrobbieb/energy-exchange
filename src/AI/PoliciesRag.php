<?php

namespace App\AI;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Store\Retriever;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\Document\Metadata;

final class PoliciesRag
{
    public function __construct(
        private readonly Retriever $policiesRetriever,
        private readonly AgentInterface $agent,
    ) {}

    private function getVectorDocProp(VectorDocument $obj, string $name): mixed
    {
        $r = new \ReflectionClass($obj);

        if (!$r->hasProperty($name)) {
            return null;
        }

        $p = $r->getProperty($name);
        return $p->getValue($obj);
    }

    /**
     * @return array{answer:string, citations:array<int,array{doc:string,section:string,score:float|null}>, sourcesUsed:int}
     */
    public function answer(string $question, int $k = 6): array
    {
        $hits = $this->policiesRetriever->retrieve($question, ['limit' => $k]);

        $sourcesText = '';
        $citations = [];
        $candidates = [];
        $i = 0;
        $maxSourcesForPrompt = 4;

        foreach ($hits as $hit) {
            if (!$hit instanceof VectorDocument) {
                // Your retriever currently returns VectorDocument hits (id/vector/metadata/score)
                // If it ever changes, we’ll handle it later.
                continue;
            }

            /** @var Metadata|null $metadata */
            $metadata = $this->getVectorDocProp($hit, 'metadata');
            $score    = $this->getVectorDocProp($hit, 'score'); // ?float

            //Lower score is what we want here. @TODO keep an eye on this, we may need to change.
            if (is_float($score) && $score > 0.6) { 
                continue; 
            }

            // Metadata in your version is ArrayAccess; no get() method.
            $docTitle = $metadata ? ($metadata['doc_title'] ?? 'Policy') : 'Policy';
            $section  = $metadata ? ($metadata['section'] ?? '') : '';

            // IMPORTANT:
            // VectorDocument does NOT include content. We rely on indexing copying chunk text into metadata['chunk'].
            $content  = $metadata ? ($metadata['chunk'] ?? '') : '';

            if (trim($content) === '') {
                // Skip empty sources so we don't feed blank context to the model
                continue;
            }

            $i++;

            $candidates[] = [
                'doc' => (string)$docTitle,
                'section' => (string)$section,
                'score' => is_float($score) ? $score : null,
                'content' => $content,
            ];
            
            $debugSources[] = [
                'doc' => (string)$docTitle,
                'section' => (string)$section,
                'preview' => mb_substr($content, 0, 200),
            ];
            $citations[] = [
                'doc' => (string) $docTitle,
                'section' => (string) $section,
                'score' => is_float($score) ? $score : null,
            ];

            // $sourcesText .= "\n\n[Source {$i}: {$docTitle} — {$section}]\n";
            // $sourcesText .= $content;
            
            if ($i <= $maxSourcesForPrompt) {
                $sourcesText .= "\n\n[Source {$i}: {$docTitle} — {$section}]\n";
                $sourcesText .= $content;
            }
        }

        if ($i === 0) {
            return [
                'answer' => "I don’t have enough information in the current policies to answer that yet.",
                'citations' => [],
                'sourcesUsed' => 0,
            ];
        }

        $messages = new MessageBag(
            Message::forSystem(
                "You are the Energy Exchange Support Copilot.\n".
                "Rules:\n".
                "1) Use ONLY the provided Sources.\n".
                "2) If the Sources do not fully answer the question, answer with what they DO say, then state what’s missing.\n".
                "3) Do not invent pricing, legal claims, refunds, or operational rules.\n".
                "4) Citations:\n".
                "   - Cite ONLY sources you actually used.\n".
                "   - Cite the MINIMUM number of sources needed.\n".
                "   - Prefer the most directly relevant sources (definitions and handling rules first).\n".
                "   - Output citations as: 'Citations: Source X, Source Y' at the end.\n".
                "5) After each paragraph that states a rule, add inline citations like (Source 2).\n"
            ),
            Message::ofUser("Question:\n{$question}\n\nSources:\n{$sourcesText}\n")
        );

        $result = $this->agent->call($messages);


        //cut off candidartes over .55
        #@TODO adjust this if necessary
        $maxScore = 0.55;
        $candidates = array_values(array_filter($candidates, fn($c) => $c['score'] === null || $c['score'] <= $maxScore));

        // sort by score asc (nulls last)
        usort($candidates, function ($a, $b) {
            $sa = $a['score']; $sb = $b['score'];
            if ($sa === null && $sb === null) return 0;
            if ($sa === null) return 1;
            if ($sb === null) return -1;
            return $sa <=> $sb;
        });

        $retrieved = $candidates;                 // all candidates (for JSON)
        $provided  = array_slice($candidates, 0, 4); // top N to feed the model

        $sourcesText = '';
        $i = 0;
        foreach ($provided as $src) {
            $i++;
            $sourcesText .= "\n\n[Source {$i}: {$src['doc']} — {$src['section']}]\n";
            $sourcesText .= $src['content'];
        }


        // $providedSources = array_map(fn($x) => [
        //     'doc' => $x['doc'],
        //     'section' => $x['section'],
        //     'score' => $x['score'],
        // ], $provided);
        $providedSources = [];
        #$sourceMap = [];
        $sourcesText = '';
        $i = 0;

        foreach ($provided as $src) {
            $i++;

            // Build sourceMap (UI-friendly)
            // $sourceMap[] = [
            //     'source' => $i,
            //     'doc' => $src['doc'],
            //     'section' => $src['section'],
            // ];
            // $sourceMap[] = [
            //     'source' => $i,
            //     'doc' => (string) $docTitle,
            //     'section' => (string) $section,
            // ];

            // Keep citations (existing API compatibility)
            $providedSources[] = [
                'doc' => $src['doc'],
                'section' => $src['section'],
                'score' => $src['score'],
            ];

            $citations[] = [
                'doc' => (string) $docTitle,
                'section' => (string) $section,
                'score' => is_float($score) ? $score : null,
            ];

            // Build LLM context
            $sourcesText .= "\n\n[Source {$i}: {$src['doc']} — {$src['section']}]\n";
            $sourcesText .= $src['content'];
        }

        $sourceMap = [];
        foreach ($providedSources as $idx => $c) {
            $sourceMap[] = [
                'source' => $idx + 1,
                'doc' => $c['doc'],
                'section' => $c['section'],
            ];
        }

        $retrievedSources = array_map(fn($x) => [
            'doc' => $x['doc'],
            'section' => $x['section'],
            'score' => $x['score'],
        ], $retrieved);

        // return [
        //     'answer' => $result->getContent(),
        //     'providedSources' => $providedSources,
        //     'retrievedSources' => $retrievedSources, // optional
        //     'sourcesUsed' => count($providedSources),
        // ];

        // return [
        //     'answer' => $result->getContent(),
        //     'citations' => $providedSources,   // ✅ only the 4 sources given to the model
        //     'sourcesUsed' => count($providedSources),
        // ];
        return [
            'answer' => $result->getContent(),
            'citations' => $providedSources,   // unchanged
            'sourceMap' => $sourceMap,         // NEW
            'sourcesUsed' => count($providedSources),
        ];
        


        // return [
        //     'answer' => $result->getContent(),
        //     'citations' => $citations,
        //     'sourcesUsed' => count($provided),          // now matches model
        //     'retrievedCount' => count($retrieved), 
        //     //'debugSources' => $debugSources,
        // ];
    }
}

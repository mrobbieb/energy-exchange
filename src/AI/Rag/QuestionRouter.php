<?php

namespace App\AI\Rag;

final class QuestionRouter
{
    private const POLICY_TERMS = [
        'refund', 'refunds', 'dispute', 'disputes', 'chargeback',
        'points', 'listing', 'listings', 'host', 'guest',
        'transfer', 'transfers', 'partial', 'failed', 'verification',
        'meter', 'metering', 'proration', 'prorate', 'resolution',
        'terms', 'policy', 'rules',
    ];

    private const ENGINEERING_TERMS = [
        'lifepo4', 'agm', 'bms', 'soc', 'voltage', 'amps', 'amp',
        'inverter', 'charger', 'mppt', 'solar', 'shore power',
        'dc', 'ac', 'kwh', 'watt', 'watts',
        'fault', 'error code', 'overcurrent', 'undervoltage',
        'battery bank', 'busbar',
    ];

    private const SAFETY_TERMS = [
        'nec', 'abyk', 'abyc', 'compliance', 'code',
        'fire', 'smoke', 'overheat', 'overheating',
        'arc', 'short', 'short circuit', 'unsafe', 'hazard', 'risk',
        'wire gauge', 'fuse size', 'breaker', 'grounding',
    ];

    public function route(string $question): RouteResult
    {
        $q = mb_strtolower($question);

        $policyHits = $this->countHits($q, self::POLICY_TERMS);
        $engHits    = $this->countHits($q, self::ENGINEERING_TERMS);
        $safetyHits = $this->countHits($q, self::SAFETY_TERMS);

        if ($safetyHits > 0) {
            return new RouteResult(Domain::SAFETY, ['safety_terms'], 0.85);
        }

        if ($policyHits > 0 && $engHits > 0) {
            return new RouteResult(Domain::HYBRID, ['policy_terms', 'engineering_terms'], 0.75);
        }

        if ($engHits > 0) {
            return new RouteResult(Domain::ENGINEERING, ['engineering_terms'], 0.75);
        }

        return new RouteResult(Domain::POLICY, ['default_policy'], 0.65);
    }

    private function countHits(string $q, array $terms): int
    {
        $hits = 0;
        foreach ($terms as $t) {
            if (str_contains($q, $t)) {
                $hits++;
            }
        }
        return $hits;
    }
}

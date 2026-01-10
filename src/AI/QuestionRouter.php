<?php

namespace App\AI;

final class QuestionRouter
{
    public function route(string $question): string
    {
        $q = mb_strtolower($question);

        // quick-and-safe keyword routing (good enough for v1)
        $engineering = [
            'lifepo4', 'lithium', 'agm', 'battery', 'batteries',
            'inverter', 'charger', 'mppt', 'solar', 'alternator',
            'awg', 'voltage', 'amps', 'ah', 'wh', 'bms',
        ];

        $policy = [
            'points', 'listing', 'transfer', 'refund', 'chargeback',
            'dispute', 'cancellation', 'verification', 'meter', 'host', 'guest',
        ];

        foreach ($engineering as $kw) {
            if (str_contains($q, $kw)) return 'engineering';
        }
        foreach ($policy as $kw) {
            if (str_contains($q, $kw)) return 'policy';
        }

        // default to policy for now (your current corpus)
        return 'policy';
    }
}

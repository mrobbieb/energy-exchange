<?php

namespace App\AI\Rag;

enum Domain: string
{
    case POLICY = 'policy';
    case ENGINEERING = 'engineering';
    case SAFETY = 'safety';
    case HYBRID = 'hybrid'; // policy + engineering
}
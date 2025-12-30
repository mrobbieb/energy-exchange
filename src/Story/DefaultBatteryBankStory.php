<?php

namespace App\Story;

use Zenstruck\Foundry\Story;
use App\Factory\BatteryBankFactory;

final class DefaultBatteryBankStory extends Story
{
    public function build(): void
    {
        BatteryBankFactory::createMany(3);
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}

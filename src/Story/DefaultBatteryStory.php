<?php

namespace App\Story;

use Zenstruck\Foundry\Story;
use App\Factory\BatteryFactory;

final class DefaultBatteryStory extends Story
{
    public function build(): void
    {
        BatteryFactory::createMany(20);
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}

<?php

namespace App\Story;

use App\Factory\EnergyTransactionFactory;
use Zenstruck\Foundry\Story;

final class DefaultEnergyTransactionStory extends Story
{
    public function build(): void
    {
        EnergyTransactionFactory::createMany(100);
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}

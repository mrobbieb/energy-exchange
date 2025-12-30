<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class DefaultUserStory extends Story
{
    public function build(): void
    {
        UserFactory::createMany(10);
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}

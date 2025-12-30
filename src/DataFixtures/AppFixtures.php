<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Story\DefaultUserStory;
use App\Story\DefaultBatteryBankStory;
use App\Story\DefaultBatteryStory;
use App\Story\DefaultEnergyTransactionStory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        DefaultUserStory::load();
        DefaultBatteryBankStory::load();
        DefaultBatteryStory::load();
        DefaultEnergyTransactionStory::load();

        // $product = new Product();
        // $manager->persist($product);
        // $story = new DefaultUserStory();
        // $manager->flush();
    }
}

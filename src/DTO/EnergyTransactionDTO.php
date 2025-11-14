<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Battery;
use App\Entity\BatteryBank;
use App\Entity\User;

final class EnergyTransactionDTO {

    #[Assert\GreaterThan(0)]
    public int $watts;

    #[Assert\Type(Battery::class)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public Battery $battery;

    #[Assert\Type(BatteryBank::class)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public BatteryBank $batteryBank;

    #[Assert\Type(User::class)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public User $user;
    
}
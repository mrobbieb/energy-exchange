<?php

namespace App\Entity;

use App\Repository\EnergyTransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: EnergyTransactionRepository::class)]
#[Broadcast]
class EnergyTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?int $watts = null;

    #[ORM\ManyToOne(inversedBy: 'energyTransaction')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Battery $battery = null;

    #[ORM\ManyToOne(inversedBy: 'energyTransaction')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'energyTransaction')]
    private ?BatteryBank $batteryBank = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getWatts(): ?int
    {
        return $this->watts;
    }

    public function setWatts(?int $watts): static
    {
        $this->watts = $watts;

        return $this;
    }

    public function getBattery(): ?Battery
    {
        return $this->battery;
    }

    public function setBattery(Battery $battery): static
    {
        $this->battery = $battery;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getBatteryBank(): ?BatteryBank
    {
        return $this->batteryBank;
    }

    public function setBatteryBank(?BatteryBank $batteryBank): static
    {
        $this->batteryBank = $batteryBank;

        return $this;
    }
}

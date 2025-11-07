<?php

namespace App\Entity;

use App\Repository\BatteryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: BatteryRepository::class)]
#[Broadcast]
class Battery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $batteryBankId = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'batteries')]
    private ?BatteryBank $BatteryBank = null;

    #[ORM\OneToOne(mappedBy: 'battery', cascade: ['persist', 'remove'])]
    private ?PowerSource $powerSource = null;

    #[ORM\OneToOne(mappedBy: 'battery', cascade: ['persist', 'remove'])]
    private ?EnergyTransaction $energyTransaction = null;

    #[ORM\ManyToOne(inversedBy: 'batteries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

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

    public function getBatteryBankId(): ?int
    {
        return $this->batteryBankId;
    }

    public function setBatteryBankId(?int $batteryBankId): static
    {
        $this->batteryBankId = $batteryBankId;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getBatteryBank(): ?BatteryBank
    {
        return $this->BatteryBank;
    }

    public function setBatteryBank(?BatteryBank $BatteryBank): static
    {
        $this->BatteryBank = $BatteryBank;

        return $this;
    }

    public function getPowerSource(): ?PowerSource
    {
        return $this->powerSource;
    }

    public function setPowerSource(?PowerSource $powerSource): static
    {
        // unset the owning side of the relation if necessary
        if ($powerSource === null && $this->powerSource !== null) {
            $this->powerSource->setBattery(null);
        }

        // set the owning side of the relation if necessary
        if ($powerSource !== null && $powerSource->getBattery() !== $this) {
            $powerSource->setBattery($this);
        }

        $this->powerSource = $powerSource;

        return $this;
    }

    public function getEnergyTransaction(): ?EnergyTransaction
    {
        return $this->energyTransaction;
    }

    public function setEnergyTransaction(EnergyTransaction $energyTransaction): static
    {
        // set the owning side of the relation if necessary
        if ($energyTransaction->getBattery() !== $this) {
            $energyTransaction->setBattery($this);
        }

        $this->energyTransaction = $energyTransaction;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}

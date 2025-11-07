<?php

namespace App\Entity;

use App\Repository\BatteryBankRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: BatteryBankRepository::class)]
#[Broadcast]

/**
 * A clean definition of a battery bank would be multiple batteries,
 *  but in our case we will allow for a single battery to make up a
 *  a battery bank. 
 * 
 *  For the purposes of this project, a battery bank is a place where
 *  power-wattage is stored after being transfered from an outside source
 *  (a Battery/Battery entity).
 * 
 */
class BatteryBank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Battery>
     */
    #[ORM\OneToMany(targetEntity: Battery::class, mappedBy: 'BatteryBank')]
    private Collection $batteries;

    #[ORM\OneToOne(mappedBy: 'batteryBank', cascade: ['persist', 'remove'])]
    private ?EnergyTransaction $energyTransaction = null;

    public function __construct()
    {
        $this->batteries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Battery>
     */
    public function getBatteries(): Collection
    {
        return $this->batteries;
    }

    public function addBattery(Battery $battery): static
    {
        if (!$this->batteries->contains($battery)) {
            $this->batteries->add($battery);
            $battery->setBatteryBank($this);
        }

        return $this;
    }

    public function removeBattery(Battery $battery): static
    {
        if ($this->batteries->removeElement($battery)) {
            // set the owning side to null (unless already changed)
            if ($battery->getBatteryBank() === $this) {
                $battery->setBatteryBank(null);
            }
        }

        return $this;
    }

    public function getEnergyTransaction(): ?EnergyTransaction
    {
        return $this->energyTransaction;
    }

    public function setEnergyTransaction(?EnergyTransaction $energyTransaction): static
    {
        // unset the owning side of the relation if necessary
        if ($energyTransaction === null && $this->energyTransaction !== null) {
            $this->energyTransaction->setBatteryBank(null);
        }

        // set the owning side of the relation if necessary
        if ($energyTransaction !== null && $energyTransaction->getBatteryBank() !== $this) {
            $energyTransaction->setBatteryBank($this);
        }

        $this->energyTransaction = $energyTransaction;

        return $this;
    }
}

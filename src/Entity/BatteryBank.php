<?php

namespace App\Entity;

use App\Repository\BatteryBankRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Entity\User;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: BatteryBankRepository::class)]
#[Broadcast]
#[ApiResource(
    normalizationContext: ['groups' => ['batteryBank:read']],
    denormalizationContext: ['groups' => ['batteryBank:write']],
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
)]
#[ApiFilter(SearchFilter::class, 
    properties: [
        'user.id' => 'exact',
        'battery.id' => 'exact'
    ]
)]
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
    #[Groups(['batteryBank:read', 'energyTransaction:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['batteryBank:read', 'energyTransaction:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['batteryBank:read', 'energyTransaction:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['batteryBank:read', 'energyTransaction:read'])]
    private ?string $description = null;

    /**
     * @var Collection<int, Battery>
     */
    #[ORM\OneToMany(targetEntity: Battery::class, mappedBy: 'BatteryBank')]
    #[Groups(['battery:read', 'batteryBank:read'])] // <-- IMPORTANT: remove 'energyTransaction:read' here
    #[MaxDepth(1)]  
    private Collection $batteries;

    /**
     * @var Collection<int, EnergyTransaction>
     */
    #[ORM\OneToMany(targetEntity: EnergyTransaction::class, mappedBy: 'batteryBank', orphanRemoval: true)]
    private Collection $energyTransactions;

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

/**
     * @return Collection<int, EnergyTransaction>
     */
    public function getEnergyTransactions(): Collection
    {
        return $this->energyTransactions;
    }

    public function addEnergyTransaction(EnergyTransaction $energyTransaction): static
    {
        if (!$this->energyTransactions->contains($energyTransaction)) {
            $this->energyTransactions->add($energyTransaction);
            $energyTransaction->setBatteryBank($this);
        }

        return $this;
    }

    public function removeEnergyTransaction(EnergyTransaction $energyTransaction): static
    {
        if ($this->energyTransactions->removeElement($energyTransaction)) {
            // set the owning side to null (unless already changed)
            if ($energyTransaction->getBatteryBank() === $this) {
                $energyTransaction->setBatteryBank(null);
            }
        }

        return $this;
    }
}

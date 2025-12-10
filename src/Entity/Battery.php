<?php

namespace App\Entity;

use App\Repository\BatteryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: BatteryRepository::class)]
#[Broadcast]
#[ApiResource(
    normalizationContext: ['groups' => ['battery:read']],
    denormalizationContext: ['groups' => ['battery:write']],
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    forceEager: false,
)]
#[ApiFilter(SearchFilter::class, 
    properties: [
        'user.id' => 'exact'
    ]
)]
class Battery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    //#[Groups(['energyTransaction:read', 'batteryBank:read', 'battery:read'])]
    #[Groups(['battery:read', 'batteryBank:read', 'battery:write', 'energyTransaction:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['battery:read', 'battery:write'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['battery:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['battery:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'batteries')]
    #[Groups(['battery:read'])]
    private ?BatteryBank $BatteryBank = null;

    #[ORM\OneToOne(mappedBy: 'battery', cascade: ['persist', 'remove'])]
    #[Groups(['battery:read'])]
    private ?PowerSource $powerSource = null;

    #[ORM\ManyToOne(inversedBy: 'batteries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['battery:read', 'battery:write'])]
    private ?User $user = null;

    /**
     * @var Collection<int, EnergyTransaction>
     */
    #[ORM\OneToMany(mappedBy: 'battery', targetEntity: EnergyTransaction::class)]
    //#[Groups(['battery:read'])] // <-- IMPORTANT: remove 'energyTransaction:read' here
    #[MaxDepth(1)]               // optional extra safety
    private Collection $energyTransactions;

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
            $energyTransaction->setBattery($this);
        }

        return $this;
    }

    public function removeEnergyTransaction(EnergyTransaction $energyTransaction): static
    {
        if ($this->energyTransactions->removeElement($energyTransaction)) {
            // set the owning side to null (unless already changed)
            if ($energyTransaction->getBattery() === $this) {
                $energyTransaction->setBattery(null);
            }
        }

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

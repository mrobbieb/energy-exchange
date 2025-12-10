<?php

namespace App\Entity;

use App\Repository\EnergyTransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\EnergyTransactionCollectionController;
use App\Entity\Battery;
use ApiPlatform\Metadata\Link;


#[ORM\Entity(repositoryClass: EnergyTransactionRepository::class)]
#[Broadcast]
#[ApiResource(
    normalizationContext: ['groups' => ['energyTransaction:read']],
    denormalizationContext: ['groups' => ['energyTransaction:write']],
    operations: [
        // Standard collection & item endpoints
        new GetCollection(),                 // GET /api/energy_transactions
        new Get(),                           // GET /api/energy_transactions/{id}
        new Post(),                          // POST /api/energy_transactions

        // 🔹 Subresource: transactions for a given battery
        new GetCollection(
            uriTemplate: '/batteries/{id}/transactions',
            uriVariables: [
                'id' => new Link(
                    fromClass: Battery::class,
                    fromProperty: 'energyTransactions', // <— matches Battery::$energyTransactions
                ),
            ],
            paginationEnabled: true,
            paginationItemsPerPage: 10,
            paginationClientItemsPerPage: true,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, 
    properties: [
        'battery.id' => 'exact',
        'user.id' => 'exact'
    ]
)]
class EnergyTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['energyTransaction:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['energyTransaction:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['energyTransaction:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['energyTransaction:read'])]
    private ?int $watts = null;

    #[ORM\ManyToOne(inversedBy: 'energyTransactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['energyTransaction:read'])]
    private ?Battery $battery = null;

    #[ORM\ManyToOne(inversedBy: 'energyTransactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['energyTransaction:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'energyTransactions')]
    #[Groups(['energyTransaction:read'])]
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

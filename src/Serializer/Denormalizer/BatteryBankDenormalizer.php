<?php
namespace App\Serializer\Denormalizer;

use App\Entity\BatteryBank;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BatteryBankDenormalizer implements DenormalizerInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

        public function denormalize(mixed $data, string $type, string $format = null, array $context = []): BatteryBank
    {
        if (!is_int($data)) {
            throw new \InvalidArgumentException('Expected an integer for batteryBank ID.');
        }

        $batteryBank = $this->entityManager->getRepository(BatteryBank::class)->find($data);

        if (!$batteryBank) {
            throw new \RuntimeException(sprintf('BatteryBank with ID %s not found.', $data));
        }

        return $batteryBank;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $type === BatteryBank::class && is_int($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            BatteryBank::class => true,
        ];
    }
}
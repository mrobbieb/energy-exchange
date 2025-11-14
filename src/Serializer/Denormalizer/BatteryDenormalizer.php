<?php
namespace App\Serializer\Denormalizer;

use App\Entity\Battery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BatteryDenormalizer implements DenormalizerInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

        public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Battery
    {
        if (!is_int($data)) {
            throw new \InvalidArgumentException('Expected an integer for battery ID.');
        }

        $battery = $this->entityManager->getRepository(Battery::class)->find($data);

        if (!$battery) {
            throw new \RuntimeException(sprintf('Battery with ID %s not found.', $data));
        }

        return $battery;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $type === Battery::class && is_int($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Battery::class => true,
        ];
    }
}
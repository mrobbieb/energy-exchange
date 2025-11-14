<?php
namespace App\Serializer\Denormalizer;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UserDenormalizer implements DenormalizerInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

        public function denormalize(mixed $data, string $type, string $format = null, array $context = []): User
    {
        if (!is_int($data)) {
            throw new \InvalidArgumentException('Expected an integer for user ID.');
        }

        $user = $this->entityManager->getRepository(User::class)->find($data);

        if (!$user) {
            throw new \RuntimeException(sprintf('User with ID %s not found.', $data));
        }

        return $user;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $type === User::class && is_int($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            User::class => true,
        ];
    }
}
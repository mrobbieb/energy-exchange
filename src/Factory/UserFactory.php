<?php

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'password' => 'password',#self::faker()->password(10, 20),
            'email' => self::faker()->safeEmail(),
            'roles' => [],
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]

    protected function initialize(): static
    {
        return $this->afterInstantiate(function(User $user): void {
            // At this moment, password contains the *plain* value from defaults()
            $plain = $user->getPassword() ?? 'password';

            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $plain)
            );
        });
    }
}

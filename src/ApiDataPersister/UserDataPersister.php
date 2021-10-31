<?php declare(strict_types = 1);

namespace App\ApiDataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\DTO\UserDto;
use App\Factory\UserFactory;
use App\Service\UserManager;

final class UserDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(private UserManager $manager, public UserFactory $factory)
    {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserDto;
    }

    public function persist($data, array $context = []): UserDto
    {
        return $this->factory->createDto($this->manager->create($data));
    }

    public function remove($data, array $context = [])
    {
    }
}

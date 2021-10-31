<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\UserDto;
use App\Factory\UserFactory;
use App\Repository\UserRepository;

final class UserItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private UserRepository $repository, private UserFactory $factory)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?UserDto
    {
        $user = $this->repository->findOneByUsername($id);

        return $user ? $this->factory->createDto($user) : null;
    }
}


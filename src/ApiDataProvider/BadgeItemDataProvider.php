<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\BadgeDto;
use App\Factory\BadgeFactory;
use App\Repository\BadgeRepository;

final class BadgeItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private BadgeRepository $repository, private BadgeFactory $factory)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return BadgeDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?BadgeDto
    {
        $badge = $this->repository->find($id);

        return $badge ? $this->factory->createDto($badge) : null;
    }
}


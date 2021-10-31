<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\EntryDto;
use App\Factory\EntryFactory;
use App\Repository\EntryRepository;

final class EntryItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private EntryRepository $repository, private EntryFactory $factory)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return EntryDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?EntryDto
    {
        $entry = $this->repository->find($id);

        return $entry ? $this->factory->createDto($entry) : null;
    }
}


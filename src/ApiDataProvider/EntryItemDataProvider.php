<?php declare(strict_types=1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use App\Repository\EntryRepository;
use App\Factory\EntryFactory;
use App\DTO\EntryDto;

final class EntryItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private EntryRepository $entryRepository, private EntryFactory $entryFactory)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return EntryDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?EntryDto
    {
        $entry = $this->entryRepository->find($id);

        if (!$entry) {
            return null;
        }

        return $this->entryFactory->createDto($entry);
    }
}


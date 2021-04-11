<?php declare(strict_types=1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Repository\EntryRepository;
use App\PageView\EntryPageView;
use App\Factory\EntryFactory;
use App\DTO\EntryDto;
use Exception;

final class EntryCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private EntryRepository $repository,
        private EntryFactory $factory,
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return EntryDto::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $criteria = new EntryPageView(1);
            $entries  = $this->repository->findByCriteria($criteria);
        } catch (Exception $e) {
            return [];
        }

        foreach ($entries as $entry) {
            yield $this->factory->createDto($entry);
        }
    }
}


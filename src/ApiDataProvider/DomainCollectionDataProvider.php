<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\DomainDto;
use App\Factory\DomainFactory;
use App\Repository\DomainRepository;
use App\Repository\EntryRepository;
use Exception;

final class DomainCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private DomainRepository $repository,
        private DomainFactory $factory,
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return DomainDto::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $domains = $this->repository->findAllPaginated(1);
        } catch (Exception $e) {
            return [];
        }

        $dtos = array_map(fn($entry) => $this->factory->createDto($entry), (array) $domains->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, EntryRepository::PER_PAGE, $domains->getNbResults());
    }
}


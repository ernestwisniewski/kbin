<?php declare(strict_types=1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\EntryDto;
use App\Factory\EntryFactory;
use App\PageView\EntryPageView;
use App\Repository\EntryRepository;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

final class EntryCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private EntryRepository $repository,
        private EntryFactory $factory,
        private RequestStack $request
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return EntryDto::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $criteria = new EntryPageView((int) $this->request->getCurrentRequest()->get('page', 1));
            $entries  = $this->repository->findByCriteria($criteria);
        } catch (Exception $e) {
            return [];
        }

        $dtos = array_map(fn($entry) => $this->factory->createDto($entry), (array) $entries->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, EntryRepository::PER_PAGE, $entries->getNbResults());
    }
}


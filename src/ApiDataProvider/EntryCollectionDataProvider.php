<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\EntryDto;
use App\Factory\EntryFactory;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

final class EntryCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private EntryRepository $repository,
        private EntryFactory $factory,
        private MagazineRepository $magazineRepository,
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
            $criteria             = new EntryPageView((int) $this->request->getCurrentRequest()->get('p', 1));
            $criteria->sortOption = $this->request->getCurrentRequest()->get('sort', Criteria::SORT_HOT);
            $criteria->time       = $criteria->resolveTime($this->request->getCurrentRequest()->get('time', Criteria::TIME_ALL));

            if ($name = $this->request->getCurrentRequest()->get('magazine')) {
                $criteria->magazine = $this->magazineRepository->findOneByName($name);
            }

            $entries = $this->repository->findByCriteria($criteria);
        } catch (Exception $e) {
            return [];
        }

        $dtos = array_map(fn($entry) => $this->factory->createDto($entry), (array) $entries->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, EntryRepository::PER_PAGE, $entries->getNbResults());
    }
}


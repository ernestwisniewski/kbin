<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\EntryCommentDto;
use App\Factory\EntryCommentFactory;
use App\PageView\EntryCommentPageView;
use App\Repository\Criteria;
use App\Repository\EntryCommentRepository;
use App\Repository\MagazineRepository;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

final class EntryCommentCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private EntryCommentRepository $repository,
        private EntryCommentFactory $factory,
        private MagazineRepository $magazineRepository,
        private RequestStack $request
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return EntryCommentDto::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $criteria             = new EntryCommentPageView((int) $this->request->getCurrentRequest()->get('p', 1));
            $criteria->sortOption = $this->request->getCurrentRequest()->get('sort', Criteria::SORT_HOT);
            $criteria->time       = $criteria->resolveTime($this->request->getCurrentRequest()->get('time', Criteria::TIME_ALL));

            if ($name = $this->request->getCurrentRequest()->get('magazine')) {
                $criteria->magazine = $this->magazineRepository->findOneByName($name);
            }

            $comments = $this->repository->findByCriteria($criteria);
        } catch (Exception $e) {
            return [];
        }

        $dtos = array_map(fn($comment) => $this->factory->createDto($comment), (array) $comments->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, EntryCommentRepository::PER_PAGE, $comments->getNbResults());
    }
}


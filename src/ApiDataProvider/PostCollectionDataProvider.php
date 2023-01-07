<?php

declare(strict_types=1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\PostDto;
use App\Factory\PostFactory;
use App\PageView\PostPageView;
use App\Repository\Criteria;
use App\Repository\MagazineRepository;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\RequestStack;

final class PostCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly int $kbinApiItemsPerPage,
        private readonly PostRepository $repository,
        private readonly PostFactory $factory,
        private readonly MagazineRepository $magazineRepository,
        private readonly RequestStack $request
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return PostDto::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $criteria = new PostPageView((int) $this->request->getCurrentRequest()->get('p', 1));
            $criteria->sortOption = $this->request->getCurrentRequest()->get('sort', Criteria::SORT_HOT);
            $criteria->time = $criteria->resolveTime(
                $this->request->getCurrentRequest()->get('time', Criteria::TIME_ALL)
            );

            if ($name = $this->request->getCurrentRequest()->get('magazine')) {
                $criteria->magazine = $this->magazineRepository->findOneByName($name);
            }

            $posts = $this->repository->findByCriteria($criteria);
        } catch (\Exception $e) {
            return [];
        }

        $dtos = array_map(fn ($post) => $this->factory->createDto($post), (array) $posts->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, $this->kbinApiItemsPerPage, $posts->getNbResults());
    }
}

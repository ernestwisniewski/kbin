<?php declare(strict_types=1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\PostDto;
use App\Factory\PostFactory;
use App\PageView\PostPageView;
use App\Repository\PostRepository;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

final class PostCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private PostRepository $repository,
        private PostFactory $factory,
        private RequestStack $request
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return PostDto::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $criteria = new PostPageView((int) $this->request->getCurrentRequest()->get('page', 1));
            $posts    = $this->repository->findByCriteria($criteria);
        } catch (Exception $e) {
            return [];
        }

        $dtos = array_map(fn($post) => $this->factory->createDto($post), (array) $posts->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, PostRepository::PER_PAGE, $posts->getNbResults());
    }
}


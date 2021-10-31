<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\SearchDto;
use App\Repository\PostCommentRepository;
use App\Service\FactoryResolver;
use App\Service\SearchManager;
use Symfony\Component\HttpFoundation\RequestStack;

final class SearchCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private SearchManager $manager,
        private FactoryResolver $resolver,
        private RequestStack $request
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return SearchDto::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $query = $this->request->getCurrentRequest()->query->get('q');

        $results = $this->manager->findPaginated($query);

        $dtos = array_map(fn($subject) => ($this->resolver->resolve($subject))->createDto($subject), (array) $results->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, PostCommentRepository::PER_PAGE, $results->getNbResults());
    }
}


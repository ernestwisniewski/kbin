<?php declare(strict_types=1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\MagazineRepository;
use App\Factory\MagazineFactory;
use App\DTO\MagazineDto;

final class MagazineCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private MagazineRepository $repository,
        private MagazineFactory $factory,
        private RequestStack $request
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return MagazineDto::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $magazines = $this->repository
                ->findAllPaginated((int) $this->request->getCurrentRequest()->get('page', 1))
                ->getCurrentPageResults();
        } catch (Exception $e) {
            return [];
        }

        foreach ($magazines as $magazine) {
            yield $this->factory->createDto($magazine);
        }
    }
}


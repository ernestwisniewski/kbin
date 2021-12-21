<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\MagazineDto;
use App\Factory\MagazineFactory;
use App\Repository\MagazineRepository;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

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
                ->findAllPaginated((int) $this->request->getCurrentRequest()->get('p', 1));
        } catch (Exception $e) {
            return [];
        }

        $dtos = array_map(fn($magazine) => $this->factory->createDto($magazine), (array) $magazines->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, MagazineRepository::PER_PAGE, $magazines->getNbResults());
    }
}

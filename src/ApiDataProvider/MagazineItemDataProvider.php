<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\MagazineDto;
use App\Factory\MagazineFactory;
use App\Repository\MagazineRepository;

final class MagazineItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MagazineRepository $repository, private MagazineFactory $factory)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return MagazineDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?MagazineDto
    {
        $magazine = $this->repository->findOneByName($id);

        return $magazine ? $this->factory->createDto($magazine) : null;
    }
}


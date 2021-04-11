<?php declare(strict_types=1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use App\Repository\MagazineRepository;
use App\Factory\MagazineFactory;
use App\DTO\MagazineDto;

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
        $magazine = $this->repository->findOneByName(['name' => $id]);

        if (!$magazine) {
            return null;
        }

        return $this->factory->createDto($magazine);
    }
}


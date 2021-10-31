<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\ImageDto;
use App\Factory\ImageFactory;
use App\Repository\ImageRepository;

final class ImageItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private ImageRepository $repository, private ImageFactory $factory)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ImageDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?ImageDto
    {
        $image = $this->repository->find($id);

        return $image ? $this->factory->createDto($image) : null;
    }
}


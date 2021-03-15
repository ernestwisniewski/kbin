<?php declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;

final class MagazineItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private MagazineRepository $magazineRepository;

    public function __construct(MagazineRepository $magazineRepository)
    {

        $this->magazineRepository = $magazineRepository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return MagazineDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Magazine
    {
        // Retrieve the blog post item from somewhere then return it or null if not found
        dd('a');
    }
}


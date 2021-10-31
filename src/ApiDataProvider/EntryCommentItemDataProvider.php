<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\EntryCommentDto;
use App\Factory\EntryCommentFactory;
use App\Repository\EntryCommentRepository;

final class EntryCommentItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private EntryCommentRepository $repository, private EntryCommentFactory $factory)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return EntryCommentDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?EntryCommentDto
    {
        $comment = $this->repository->find($id);

        return $comment ? $this->factory->createDto($comment) : null;
    }
}


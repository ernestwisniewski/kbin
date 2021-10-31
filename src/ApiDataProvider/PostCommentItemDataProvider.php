<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DTO\PostCommentDto;
use App\Factory\PostCommentFactory;
use App\Repository\PostCommentRepository;

final class PostCommentItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private PostCommentRepository $repository, private PostCommentFactory $factory)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return PostCommentDto::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?PostCommentDto
    {
        $comment = $this->repository->find($id);

        return $comment ? $this->factory->createDto($comment) : null;
    }
}


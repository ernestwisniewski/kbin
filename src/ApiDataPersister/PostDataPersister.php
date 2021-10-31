<?php declare(strict_types = 1);

namespace App\ApiDataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\DTO\PostDto;
use App\Factory\PostFactory;
use App\Service\PostManager;
use Symfony\Component\Security\Core\Security;

final class PostDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private PostManager $manager,
        private PostFactory $factory,
        private Security $security,
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof PostDto;
    }

    public function persist($data, array $context = []): PostDto
    {
        return $this->factory->createDto($this->manager->create($data, $this->security->getToken()->getUser()));
    }

    public function remove($data, array $context = [])
    {
    }
}

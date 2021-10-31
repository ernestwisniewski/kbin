<?php declare(strict_types = 1);

namespace App\ApiDataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\DTO\EntryCommentDto;
use App\Factory\EntryCommentFactory;
use App\Service\EntryCommentManager;
use Symfony\Component\Security\Core\Security;

final class EntryCommentDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private EntryCommentManager $manager,
        private EntryCommentFactory $factory,
        private Security $security,
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof EntryCommentDto;
    }

    public function persist($data, array $context = []): EntryCommentDto
    {
        return $this->factory->createDto($this->manager->create($data, $this->security->getToken()->getUser()));
    }

    public function remove($data, array $context = [])
    {
    }
}

<?php declare(strict_types = 1);

namespace App\ApiDataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\DTO\EntryDto;
use App\Factory\EntryFactory;
use App\Service\EntryManager;
use Symfony\Component\Security\Core\Security;

final class EntryDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private EntryManager $manager,
        private EntryFactory $factory,
        private Security $security,
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof EntryDto;
    }

    public function persist($data, array $context = []): EntryDto
    {
        return $this->factory->createDto($this->manager->create($data, $this->security->getToken()->getUser()));
    }

    public function remove($data, array $context = [])
    {
    }
}

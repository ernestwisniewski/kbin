<?php

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Entity\User;
use App\Event\Entry\EntryEditedEvent;
use App\Event\Entry\EntryRestoredEvent;
use App\Kbin\Contract\RestoreContentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class EntryRestore implements RestoreContentServiceInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, ContentInterface|Entry $subject): void
    {
        if (VisibilityInterface::VISIBILITY_TRASHED !== $subject->getVisibility()) {
            throw new \Exception('Invalid visibility');
        }

        $subject->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($subject);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryRestoredEvent($subject, $user));
        $this->eventDispatcher->dispatch(new EntryEditedEvent($subject));
    }
}

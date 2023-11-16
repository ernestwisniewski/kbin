<?php

declare(strict_types=1);

namespace App\Kbin\EntryComment;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\EntryComment;
use App\Entity\User;
use App\Event\EntryComment\EntryCommentRestoredEvent;
use App\Kbin\Contracts\RestoreServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class EntryCommentRestore implements RestoreServiceInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, ContentInterface|EntryComment $subject): void
    {
        if (VisibilityInterface::VISIBILITY_TRASHED !== $subject->getVisibility()) {
            throw new \Exception('Invalid visibility');
        }

        $subject->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($subject);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryCommentRestoredEvent($subject, $user));
    }
}

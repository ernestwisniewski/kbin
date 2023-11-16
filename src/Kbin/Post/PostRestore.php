<?php

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Event\Post\PostRestoredEvent;
use App\Kbin\Contracts\RestoreServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostRestore implements RestoreServiceInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, ContentInterface|Post $subject): void
    {
        if (VisibilityInterface::VISIBILITY_TRASHED !== $subject->getVisibility()) {
            throw new \Exception('Invalid visibility');
        }

        $subject->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($subject);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostRestoredEvent($subject, $user));
    }
}

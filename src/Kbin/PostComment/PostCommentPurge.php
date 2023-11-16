<?php

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\Entity\PostComment;
use App\Entity\User;
use App\Event\PostComment\PostCommentBeforePurgeEvent;
use App\Event\PostComment\PostCommentPurgedEvent;
use App\Message\DeleteImageMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PostCommentPurge
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, PostComment $comment): void
    {
        $this->eventDispatcher->dispatch(new PostCommentBeforePurgeEvent($comment, $user));

        $magazine = $comment->post->magazine;
        $image = $comment->image?->filePath;
        $comment->post->removeComment($comment);
        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostCommentPurgedEvent($magazine));

        if ($image) {
            $this->messageBus->dispatch(new DeleteImageMessage($image));
        }
    }
}

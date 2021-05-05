<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\PostCommentDto;
use App\Entity\PostComment;
use App\Entity\User;
use App\Event\PostComment\PostCommentBeforePurgeEvent;
use App\Event\PostComment\PostCommentCreatedEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Event\PostComment\PostCommentPurgedEvent;
use App\Event\PostComment\PostCommentUpdatedEvent;
use App\Factory\PostCommentFactory;
use App\Service\Contracts\ContentManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class PostCommentManager implements ContentManager
{
    public function __construct(
        private PostCommentFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostCommentDto $dto, User $user): PostComment
    {
        $comment = $this->factory->createFromDto($dto, $user);

        $comment->post->addComment($comment);
        $comment->magazine = $dto->post->magazine;
        if ($dto->image) {
            $comment->image = $dto->image;
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentCreatedEvent($comment));

        return $comment;
    }

    public function edit(PostComment $comment, PostCommentDto $dto): PostComment
    {
        Assert::same($comment->post->getId(), $dto->post->getId());

        $comment->body = $dto->body;
        if ($dto->image) {
            $comment->image = $dto->image;
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentUpdatedEvent($comment));

        return $comment;
    }

    public function delete(User $user, PostComment $comment): void
    {
        $this->isTrashed($user, $comment) ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentDeletedEvent($comment, $user));
    }

    private function isTrashed(User $user, PostComment $comment): bool
    {
        return !$comment->isAuthor($user);
    }

    public function purge(PostComment $comment): void
    {
        $this->dispatcher->dispatch(new PostCommentBeforePurgeEvent($comment));

        $magazine = $comment->post->magazine;
        $comment->post->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentPurgedEvent($magazine));
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        return $this->factory->createDto($comment);
    }
}

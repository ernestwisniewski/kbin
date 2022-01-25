<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\PostCommentDto;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\PostComment;
use App\Entity\User;
use App\Event\PostComment\PostCommentBeforePurgeEvent;
use App\Event\PostComment\PostCommentCreatedEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Event\PostComment\PostCommentEditedEvent;
use App\Event\PostComment\PostCommentPurgedEvent;
use App\Event\PostComment\PostCommentRestoredEvent;
use App\Factory\PostCommentFactory;
use App\Message\DeleteImageMessage;
use App\Service\Contracts\ContentManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Webmozart\Assert\Assert;

class PostCommentManager implements ContentManagerInterface
{
    public function __construct(
        private TagManager $tagManager,
        private PostCommentFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private RateLimiterFactory $postCommentLimiter,
        private MessageBusInterface $bus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostCommentDto $dto, User $user): PostComment
    {
        $limiter = $this->postCommentLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $comment = $this->factory->createFromDto($dto, $user);

        $comment->magazine             = $dto->post->magazine;
        $comment->image                = $dto->image;
        $comment->tags                 = $this->tagManager->extract($comment->body);
        $comment->magazine->lastActive = new \DateTime();
        $comment->post->addComment($comment);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentCreatedEvent($comment));

        return $comment;
    }

    public function edit(PostComment $comment, PostCommentDto $dto): PostComment
    {
        Assert::same($comment->post->getId(), $dto->post->getId());

        $comment->body  = $dto->body;
        $oldImage       = $comment->image;
        $comment->image = $dto->image;
        $comment->tags  = $this->tagManager->extract($comment->body);

        $this->entityManager->flush();

        if ($oldImage && $dto->image !== $oldImage) {
            $this->bus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->dispatcher->dispatch(new PostCommentEditedEvent($comment));

        return $comment;
    }

    public function delete(User $user, PostComment $comment): void
    {
        if ($comment->isAuthor($user) && $comment->children->isEmpty()) {
            $this->purge($comment);

            return;
        }

        $this->isTrashed($user, $comment) ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentDeletedEvent($comment, $user));
    }

    public function restore(User $user, PostComment $comment): void
    {
        if ($comment->visibility !== VisibilityInterface::VISIBILITY_TRASHED) {
            throw new \Exception('Invalid visibility');
        }

        $comment->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentRestoredEvent($comment, $user));
    }

    public function purge(PostComment $comment): void
    {
        $this->dispatcher->dispatch(new PostCommentBeforePurgeEvent($comment));

        $magazine = $comment->post->magazine;
        $image    = $comment->image?->filePath;
        $comment->post->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentPurgedEvent($magazine));

        if ($image) {
            $this->bus->dispatch(new DeleteImageMessage($image));
        }
    }

    private function isTrashed(User $user, PostComment $comment): bool
    {
        return !$comment->isAuthor($user);
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        return $this->factory->createDto($comment);
    }

    public function detachImage(PostComment $comment): void
    {
        $image = $comment->image->filePath;

        $comment->image = null;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteImageMessage($image));
    }
}

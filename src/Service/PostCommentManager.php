<?php declare(strict_types=1);

namespace App\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use App\Event\PostCommentBeforePurgeEvent;
use App\Service\Contracts\ContentManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\PostCommentUpdatedEvent;
use App\Event\PostCommentCreatedEvent;
use App\Event\PostCommentDeletedEvent;
use App\Event\PostCommentPurgedEvent;
use App\Factory\PostCommentFactory;
use Webmozart\Assert\Assert;
use App\Entity\PostComment;
use App\DTO\PostCommentDto;
use App\Entity\User;

class PostCommentManager implements ContentManager
{
    public function __construct(
        private PostCommentFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private Security $security,
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

        $this->dispatcher->dispatch((new PostCommentCreatedEvent($comment)));

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

        $this->dispatcher->dispatch((new PostCommentUpdatedEvent($comment)));

        return $comment;
    }

    public function delete(PostComment $comment, bool $trash = false): void
    {
        $trash ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch((new PostCommentDeletedEvent($comment, $this->security->getUser())));
    }

    public function purge(PostComment $comment): void
    {
        $this->dispatcher->dispatch((new PostCommentBeforePurgeEvent($comment)));

        $magazine = $comment->post->magazine;
        $comment->post->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch((new PostCommentPurgedEvent($magazine)));
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        return $this->factory->createDto($comment);
    }
}

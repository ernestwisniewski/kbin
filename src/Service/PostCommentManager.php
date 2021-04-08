<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\PostCommentDto;
use App\Entity\PostComment;
use App\Event\PostCommentBeforePurgeEvent;
use App\Event\PostCommentCreatedEvent;
use App\Event\PostCommentDeletedEvent;
use App\Event\PostCommentPurgedEvent;
use App\Event\PostCommentUpdatedEvent;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Service\Contracts\ContentManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\PostCommentFactory;
use Symfony\Component\Security\Core\Security;
use Webmozart\Assert\Assert;
use App\Entity\User;

class PostCommentManager implements ContentManager
{
    public function __construct(
        private PostCommentFactory $commentFactory,
        private EventDispatcherInterface $eventDispatcher,
        private PostCommentRepository $commentRepository,
        private PostRepository $postRepository,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostCommentDto $commentDto, User $user): PostComment
    {
        $comment = $this->commentFactory->createFromDto($commentDto, $user);

        $comment->getPost()->addComment($comment);
        $comment->setMagazine($commentDto->post->getMagazine());
        if ($commentDto->image) {
            $comment->setImage($commentDto->getImage());
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostCommentCreatedEvent($comment)));

        return $comment;
    }

    public function edit(PostComment $comment, PostCommentDto $commentDto): PostComment
    {
        Assert::same($comment->getPost()->getId(), $commentDto->post->getId());

        $comment->setBody($commentDto->body);
        if ($commentDto->image) {
            $comment->setImage($commentDto->image);
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostCommentUpdatedEvent($comment)));

        return $comment;
    }

    public function delete(PostComment $comment, bool $trash = false): void
    {
        $trash ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostCommentDeletedEvent($comment, $this->security->getUser())));
    }

    public function purge(PostComment $comment): void
    {
        $this->eventDispatcher->dispatch((new PostCommentBeforePurgeEvent($comment)));

        $magazine = $comment->getPost()->getMagazine();
        $comment->getPost()->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostCommentPurgedEvent($magazine)));
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        return $this->commentFactory->createDto($comment);
    }
}

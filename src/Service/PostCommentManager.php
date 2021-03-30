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
    private PostCommentFactory $commentFactory;
    private EventDispatcherInterface $eventDispatcher;
    private PostCommentRepository $commentRepository;
    private PostRepository $postRepository;
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PostCommentFactory $commentFactory,
        EventDispatcherInterface $eventDispatcher,
        PostCommentRepository $commentRepository,
        PostRepository $postRepository,
        Security $security,
        EntityManagerInterface $entityManager
    ) {
        $this->commentFactory    = $commentFactory;
        $this->eventDispatcher   = $eventDispatcher;
        $this->commentRepository = $commentRepository;
        $this->postRepository    = $postRepository;
        $this->security          = $security;
        $this->entityManager     = $entityManager;
    }

    public function create(PostCommentDto $commentDto, User $user): PostComment
    {
        $comment = $this->commentFactory->createFromDto($commentDto, $user);

        $comment->getPost()->addComment($comment);
        $comment->setMagazine($commentDto->getPost()->getMagazine());
        if ($commentDto->getImage()) {
            $comment->setImage($commentDto->getImage());
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostCommentCreatedEvent($comment)));

        return $comment;
    }

    public function edit(PostComment $comment, PostCommentDto $commentDto): PostComment
    {
        Assert::same($comment->getPost()->getId(), $commentDto->getPost()->getId());

        $comment->setBody($commentDto->getBody());
        if ($commentDto->getImage()) {
            $comment->setImage($commentDto->getImage());
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

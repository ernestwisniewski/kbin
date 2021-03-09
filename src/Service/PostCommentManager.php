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
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\PostCommentFactory;
use Webmozart\Assert\Assert;
use App\Entity\User;

class PostCommentManager implements ContentManager
{
    private PostCommentFactory $commentFactory;
    private EventDispatcherInterface $eventDispatcher;
    private MessageBusInterface $messageBus;
    private PostCommentRepository $commentRepository;
    private PostRepository $postRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PostCommentFactory $commentFactory,
        EventDispatcherInterface $eventDispatcher,
        MessageBusInterface $messageBus,
        PostCommentRepository $commentRepository,
        PostRepository $postRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->commentFactory    = $commentFactory;
        $this->eventDispatcher   = $eventDispatcher;
        $this->messageBus        = $messageBus;
        $this->commentRepository = $commentRepository;
        $this->postRepository    = $postRepository;
        $this->entityManager     = $entityManager;
    }

    public function create(PostCommentDto $commentDto, User $user): PostComment
    {
        $comment = $this->commentFactory->createFromDto($commentDto, $user);

        $comment->getPost()->addComment($comment);
        $comment->setMagazine($commentDto->getPost()->getMagazine());

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostCommentCreatedEvent($comment)));

        return $comment;
    }

    public function edit(PostComment $comment, PostCommentDto $commentDto): PostComment
    {
        Assert::same($comment->getPost()->getId(), $commentDto->getPost()->getId());

        $comment->setBody($commentDto->getBody());

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostCommentUpdatedEvent($comment)));

        return $comment;
    }

    public function delete(PostComment $comment, bool $trash = false): void
    {
        if ($comment->getChildren()->count()) {
            $trash ? $comment->trash() : $comment->softDelete();
        } else {
            $this->purge($comment);

            return;
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostCommentDeletedEvent($comment)));
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

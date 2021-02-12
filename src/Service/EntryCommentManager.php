<?php declare(strict_types = 1);

namespace App\Service;

use Symfony\Component\Messenger\MessageBusInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Repository\EntryCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\EntryCommentCreatedEvent;
use App\Event\EntryCommentPurgedEvent;
use App\Event\EntryCommentUpdatedEvent;
use App\Factory\EntryCommentFactory;
use App\Repository\EntryRepository;
use Webmozart\Assert\Assert;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\User;

class EntryCommentManager
{
    private EntryCommentFactory $commentFactory;
    private EventDispatcherInterface $eventDispatcher;
    private MessageBusInterface $messageBus;
    private EntryCommentRepository $commentRepository;
    private EntryRepository $entryRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntryCommentFactory $commentFactory,
        EventDispatcherInterface $eventDispatcher,
        MessageBusInterface $messageBus,
        EntryCommentRepository $commentRepository,
        EntryRepository $entryRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->commentFactory    = $commentFactory;
        $this->eventDispatcher   = $eventDispatcher;
        $this->messageBus        = $messageBus;
        $this->commentRepository = $commentRepository;
        $this->entryRepository   = $entryRepository;
        $this->entityManager     = $entityManager;
    }

    public function create(EntryCommentDto $commentDto, User $user): EntryComment
    {
        $comment = $this->commentFactory->createFromDto($commentDto, $user);

        $comment->getEntry()->addComment($comment);
        $comment->setRoot($commentDto->getRoot());

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentCreatedEvent($comment)));

        return $comment;
    }

    public function edit(EntryComment $comment, EntryCommentDto $commentDto): EntryComment
    {
        Assert::same($comment->getEntry()->getId(), $commentDto->getEntry()->getId());

        $comment->setBody($commentDto->getBody());

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentUpdatedEvent($comment)));

        return $comment;
    }

    public function purge(EntryComment $comment): void
    {
        $magazine = $comment->getEntry()->getMagazine();
        $comment->getEntry()->removeComment($comment);

        $this->entityManager->remove($comment);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentPurgedEvent($magazine)));
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        return $this->commentFactory->createDto($comment);
    }
}

<?php declare(strict_types=1);

namespace App\Service;

use App\Event\EntryCommentBeforePurgeEvent;
use App\Event\EntryCommentDeletedEvent;
use App\Service\Contracts\ContentManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Repository\EntryCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\EntryCommentCreatedEvent;
use App\Event\EntryCommentPurgedEvent;
use App\Event\EntryCommentUpdatedEvent;
use App\Factory\EntryCommentFactory;
use App\Repository\EntryRepository;
use Symfony\Component\Security\Core\Security;
use Webmozart\Assert\Assert;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\User;

class EntryCommentManager implements ContentManager
{
    public function __construct(
        private EntryCommentFactory $commentFactory,
        private EventDispatcherInterface $eventDispatcher,
        private EntryCommentRepository $commentRepository,
        private EntryRepository $entryRepository,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryCommentDto $commentDto, User $user): EntryComment
    {
        $comment = $this->commentFactory->createFromDto($commentDto, $user);

        $comment->getEntry()->addComment($comment);
        $comment->setMagazine($commentDto->getEntry()->getMagazine());

        if ($commentDto->getImage()) {
            $comment->setImage($commentDto->getImage());
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentCreatedEvent($comment)));

        return $comment;
    }

    public function edit(EntryComment $comment, EntryCommentDto $commentDto): EntryComment
    {
        Assert::same($comment->getEntry()->getId(), $commentDto->getEntry()->getId());

        $comment->setBody($commentDto->getBody());
        if ($commentDto->getImage()) {
            $comment->setImage($commentDto->getImage());
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentUpdatedEvent($comment)));

        return $comment;
    }

    public function delete(EntryComment $comment, bool $trash = false): void
    {
        $trash ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentDeletedEvent($comment, $this->security->getUser())));
    }

    public function purge(EntryComment $comment): void
    {
        $this->eventDispatcher->dispatch((new EntryCommentBeforePurgeEvent($comment)));

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

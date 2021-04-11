<?php declare(strict_types=1);

namespace App\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use App\Event\EntryCommentBeforePurgeEvent;
use App\Service\Contracts\ContentManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\EntryCommentDeletedEvent;
use App\Event\EntryCommentCreatedEvent;
use App\Event\EntryCommentUpdatedEvent;
use App\Event\EntryCommentPurgedEvent;
use App\Factory\EntryCommentFactory;
use Webmozart\Assert\Assert;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\User;

class EntryCommentManager implements ContentManager
{
    public function __construct(
        private EntryCommentFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryCommentDto $dto, User $user): EntryComment
    {
        $comment = $this->factory->createFromDto($dto, $user);

        $comment->entry->addComment($comment);
        $comment->magazine = $dto->entry->magazine;

        if ($dto->image) {
            $comment->image = $dto->image;
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch((new EntryCommentCreatedEvent($comment)));

        return $comment;
    }

    public function edit(EntryComment $comment, EntryCommentDto $dto): EntryComment
    {
        Assert::same($comment->entry->getId(), $dto->entry->getId());

        $comment->body = $dto->body;
        if ($dto->image) {
            $comment->image = $dto->image;
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch((new EntryCommentUpdatedEvent($comment)));

        return $comment;
    }

    public function delete(EntryComment $comment, bool $trash = false): void
    {
        $trash ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch((new EntryCommentDeletedEvent($comment, $this->security->getUser())));
    }

    public function purge(EntryComment $comment): void
    {
        $this->dispatcher->dispatch((new EntryCommentBeforePurgeEvent($comment)));

        $magazine = $comment->entry->magazine;
        $comment->entry->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch((new EntryCommentPurgedEvent($magazine)));
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        return $this->factory->createDto($comment);
    }
}

<?php

declare(strict_types=1);

namespace App\Kbin\EntryComment;

use App\Entity\EntryComment;
use App\Event\EntryComment\EntryCommentEditedEvent;
use App\Kbin\EntryComment\DTO\EntryCommentDto;
use App\Message\DeleteImageMessage;
use App\Repository\ImageRepository;
use App\Service\MentionManager;
use App\Service\TagManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

readonly class EntryCommentEdit
{
    public function __construct(
        private TagManager $tagManager,
        private MentionManager $mentionManager,
        private ImageRepository $imageRepository,
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(EntryComment $comment, EntryCommentDto $dto): EntryComment
    {
        Assert::same($comment->entry->getId(), $dto->entry->getId());

        $comment->body = $dto->body;
        $comment->lang = $dto->lang;
        $comment->isAdult = $dto->isAdult || $comment->magazine->isAdult;
        $oldImage = $comment->image;
        if ($dto->image && $dto->image->id !== $comment->image->getId()) {
            $comment->image = $this->imageRepository->find($dto->image->id);
        }
        $comment->tags = $dto->body ? $this->tagManager->extract($dto->body, $comment->magazine->name) : null;
        $comment->mentions = $dto->body
            ? array_merge($dto->mentions ?? [], $this->mentionManager->handleChain($comment))
            : $dto->mentions;
        $comment->visibility = $dto->visibility;
        $comment->editedAt = new \DateTimeImmutable('@'.time());
        if (empty($comment->body) && null === $comment->image) {
            throw new \Exception('Comment body and image cannot be empty');
        }

        $this->entityManager->flush();

        if ($oldImage && $comment->image !== $oldImage) {
            $this->messageBus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->eventDispatcher->dispatch(new EntryCommentEditedEvent($comment));

        return $comment;
    }
}

<?php

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\Entity\PostComment;
use App\Event\PostComment\PostCommentEditedEvent;
use App\Kbin\PostComment\DTO\PostCommentDto;
use App\Message\DeleteImageMessage;
use App\Repository\ImageRepository;
use App\Service\MentionManager;
use App\Service\TagManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

readonly class PostCommentEdit
{
    public function __construct(
        private TagManager $tagManager,
        private MentionManager $mentionManager,
        private ImageRepository $imageRepository,
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(PostComment $comment, PostCommentDto $dto): PostComment
    {
        Assert::same($comment->post->getId(), $dto->post->getId());

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
        $comment->visibility = $dto->getVisibility();
        $comment->editedAt = new \DateTimeImmutable('@'.time());
        if (empty($comment->body) && null === $comment->image) {
            throw new \Exception('Comment body and image cannot be empty');
        }

        $this->entityManager->flush();

        if ($oldImage && $comment->image !== $oldImage) {
            $this->messageBus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->eventDispatcher->dispatch(new PostCommentEditedEvent($comment));

        return $comment;
    }
}

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Kbin\MessageBus\ImagePurgeMessage;
use App\Kbin\Post\DTO\PostDto;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use App\Kbin\Tag\TagExtract;
use App\Repository\ImageRepository;
use App\Service\MentionManager;
use App\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

readonly class PostEdit
{
    public function __construct(
        private Slugger $slugger,
        private MentionManager $mentionManager,
        private TagExtract $tagExtract,
        private ImageRepository $imageRepository,
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post, PostDto $dto): Post
    {
        Assert::same($post->magazine->getId(), $dto->magazine->getId());

        $post->body = $dto->body;
        $post->lang = $dto->lang;
        $post->isAdult = $dto->isAdult || $post->magazine->isAdult;
        $post->slug = $this->slugger->slug($dto->body ?? $dto->magazine->name.' '.$dto->image->altText);
        $oldImage = $post->image;
        if ($dto->image && $dto->image->id !== $post->image?->getId()) {
            $post->image = $this->imageRepository->find($dto->image->id);
        }
        $post->tags = $dto->body ? ($this->tagExtract)($dto->body, $post->magazine->name) : null;
        $post->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $post->visibility = $dto->getVisibility();
        $post->editedAt = new \DateTimeImmutable('@'.time());
        if (empty($post->body) && null === $post->image) {
            throw new \Exception('Post body and image cannot be empty');
        }

        $this->entityManager->flush();

        if ($oldImage && $post->image !== $oldImage) {
            $this->messageBus->dispatch(new ImagePurgeMessage($oldImage->filePath));
        }

        $this->eventDispatcher->dispatch(new PostEditedEvent($post));

        return $post;
    }
}

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Kbin\Entry\EntryEdit;
use App\Kbin\Entry\Factory\EntryFactory;
use App\Kbin\EntryComment\EntryCommentEdit;
use App\Kbin\EntryComment\Factory\EntryCommentFactory;
use App\Kbin\Post\Factory\PostFactory;
use App\Kbin\Post\PostEdit;
use App\Kbin\PostComment\Factory\PostCommentFactory;
use App\Kbin\PostComment\PostCommentEdit;
use App\Message\ActivityPub\Inbox\UpdateMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\MarkdownConverter;
use App\Service\ActivityPubManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateHandler
{
    private array $payload;

    public function __construct(
        private readonly ActivityPubManager $activityPubManager,
        private readonly ApActivityRepository $apActivityRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryEdit $entryEdit,
        private readonly EntryCommentEdit $entryCommentEdit,
        private readonly PostEdit $postEdit,
        private readonly PostCommentEdit $postCommentEdit,
        private readonly MarkdownConverter $markdownConverter,
        private readonly EntryFactory $entryFactory,
        private readonly EntryCommentFactory $entryCommentFactory,
        private readonly PostFactory $postFactory,
        private readonly PostCommentFactory $postCommentFactory,
    ) {
    }

    public function __invoke(UpdateMessage $message): void
    {
        $this->payload = $message->payload;

        try {
            $actor = $this->activityPubManager->findRemoteActor($message->payload['actor']);
        } catch (Exception) {
            return;
        }

        $object = $this->apActivityRepository->findByObjectId($message->payload['object']['id']);

        if (!$object) {
            return;
        }

        $object = $this->entityManager->getRepository($object['type'])->find((int) $object['id']);

        if (Entry::class === \get_class($object)) {
            $fn = 'entry';
        }

        if (EntryComment::class === \get_class($object)) {
            $fn = 'entryComment';
        }

        if (Post::class === \get_class($object)) {
            $fn = 'post';
        }

        if (PostComment::class === \get_class($object)) {
            $fn = 'postComment';
        }

        $this->$fn($object, $actor);
    }

    private function entry(Entry $entry, User $user): void
    {
        $dto = $this->entryFactory->createDto($entry);

        $dto->title = $this->payload['object']['name'];

        if (!empty($this->payload['object']['content'])) {
            $dto->body = $this->markdownConverter->convert($this->payload['object']['content']);
        } else {
            $dto->body = null;
        }

        ($this->entryEdit)($entry, $dto);
    }

    private function entryComment(EntryComment $comment, User $user): void
    {
        $dto = $this->entryCommentFactory->createDto($comment);

        if (!empty($this->payload['object']['content'])) {
            $dto->body = $this->markdownConverter->convert($this->payload['object']['content']);
        } else {
            $dto->body = null;
        }

        ($this->entryCommentEdit)($comment, $dto);
    }

    private function post(Post $post, User $user): void
    {
        $dto = $this->postFactory->createDto($post);

        if (!empty($this->payload['object']['content'])) {
            $dto->body = $this->markdownConverter->convert($this->payload['object']['content']);
        } else {
            $dto->body = null;
        }

        ($this->postEdit)($post, $dto);
    }

    private function postComment(PostComment $comment, User $user): void
    {
        $dto = $this->postCommentFactory->createDto($comment);

        if (!empty($this->payload['object']['content'])) {
            $dto->body = $this->markdownConverter->convert($this->payload['object']['content']);
        } else {
            $dto->body = null;
        }

        ($this->postCommentEdit)($comment, $dto);
    }
}

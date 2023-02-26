<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Factory\EntryCommentFactory;
use App\Factory\EntryFactory;
use App\Factory\PostCommentFactory;
use App\Factory\PostFactory;
use App\Message\ActivityPub\Inbox\UpdateMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\MarkdownConverter;
use App\Service\ActivityPubManager;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateHandler implements MessageHandlerInterface
{
    private array $payload;

    public function __construct(
        private readonly ActivityPubManager $activityPubManager,
        private readonly ApActivityRepository $apActivityRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryManager $entryManager,
        private readonly EntryCommentManager $entryCommentManager,
        private readonly PostManager $postManager,
        private readonly PostCommentManager $postCommentManager,
        private readonly MarkdownConverter $markdownConverter,
        private readonly EntryFactory $entryFactory,
        private readonly EntryCommentFactory $entryCommentFactory,
        private readonly PostFactory $postFactory,
        private readonly PostCommentFactory $postCommentFactory,
        private readonly MessageBusInterface $bus,
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

        if (Entry::class === get_class($object)) {
            $fn = 'editEntry';
        }

        if (EntryComment::class === get_class($object)) {
            $fn = 'editEntryComment';
        }

        if (Post::class === get_class($object)) {
            $fn = 'editPost';
        }

        if (PostComment::class === get_class($object)) {
            $fn = 'editPostComment';
        }

        $this->$fn($object, $actor);

//        if (null === $object->magazine->apId) {
//            $this->bus->dispatch(
//                new \App\Message\ActivityPub\Outbox\UpdateMessage(
//                    $actor->getId(),
//                    $object->getId(),
//                    get_class($object)
//                )
//            );
//        }
    }

    private function editEntry(Entry $entry, User $user)
    {
        $dto = $this->entryFactory->createDto($entry);

        $dto->title = $this->payload['object']['name'];

        if (!empty($this->payload['object']['content'])) {
            $dto->body = $this->markdownConverter->convert($this->payload['object']['content']);
        } else {
            $dto->body = null;
        }

        $this->entryManager->edit($entry, $dto);
    }

    private function editEntryComment(EntryComment $comment, User $user)
    {
        $dto = $this->entryCommentFactory->createDto($comment);

        if (!empty($this->payload['object']['content'])) {
            $dto->body = $this->markdownConverter->convert($this->payload['object']['content']);
        } else {
            $dto->body = null;
        }

        $this->entryCommentManager->edit($comment, $dto);
    }

    private function editPost(Post $post, User $user)
    {
        $dto = $this->postFactory->createDto($post);

        if (!empty($this->payload['object']['content'])) {
            $dto->body = $this->markdownConverter->convert($this->payload['object']['content']);
        } else {
            $dto->body = null;
        }

        $this->postManager->edit($post, $dto);
    }

    private function editPostComment(PostComment $comment, User $user)
    {
        $dto = $this->postCommentFactory->createDto($comment);

        if (!empty($this->payload['object']['content'])) {
            $dto->body = $this->markdownConverter->convert($this->payload['object']['content']);
        } else {
            $dto->body = null;
        }

        $this->postCommentManager->edit($comment, $dto);
    }
}

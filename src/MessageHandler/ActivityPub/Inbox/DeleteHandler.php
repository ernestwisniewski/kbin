<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Kbin\Entry\EntryDelete;
use App\Kbin\EntryComment\EntryCommentDelete;
use App\Kbin\Post\PostDelete;
use App\Kbin\PostComment\PostCommentDelete;
use App\Message\ActivityPub\Inbox\DeleteMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPubManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteHandler
{
    public function __construct(
        private readonly ActivityPubManager $activityPubManager,
        private readonly ApActivityRepository $apActivityRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryDelete $entryDelete,
        private readonly EntryCommentDelete $entryCommentDelete,
        private readonly PostDelete $postDelete,
        private readonly PostCommentDelete $postCommentDelete
    ) {
    }

    public function __invoke(DeleteMessage $message): void
    {
        try {
            $actor = $this->activityPubManager->findRemoteActor($message->payload['actor']);
        } catch (Exception) {
            return;
        }

        if (\is_array($message->payload['object'])) {
            $object = $this->apActivityRepository->findByObjectId($message->payload['object']['id']);
        } else {
            $object = $this->apActivityRepository->findByObjectId($message->payload['object']);
        }

        if (!$object) {
            return;
        }

        $object = $this->entityManager->getRepository($object['type'])->find((int) $object['id']);

        if (Entry::class === \get_class($object)) {
            $fn = 'deleteEntry';
        }

        if (EntryComment::class === \get_class($object)) {
            $fn = 'deleteEntryComment';
        }

        if (Post::class === \get_class($object)) {
            $fn = 'deletePost';
        }

        if (PostComment::class === \get_class($object)) {
            $fn = 'deletePostComment';
        }

        $this->$fn($object, $actor);
    }

    private function deleteEntry(Entry $entry, User $user): void
    {
        ($this->entryDelete)($user, $entry);
    }

    private function deleteEntryComment(EntryComment $comment, User $user): void
    {
        ($this->entryCommentDelete)($user, $comment);
    }

    private function deletePost(Post $post, User $user): void
    {
        ($this->postDelete)($user, $post);
    }

    private function deletePostComment(PostComment $comment, User $user): void
    {
        ($this->postCommentDelete)($user, $comment);
    }
}

<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Message\ActivityPub\Inbox\DeleteMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPubManager;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteHandler implements MessageHandlerInterface
{
    public function __construct(
        private ActivityPubManager $activityPubManager,
        private ApActivityRepository $apActivityrepository,
        private EntityManagerInterface $entityManager,
        private EntryManager $entryManager,
        private EntryCommentManager $entryCommentManager,
        private PostManager $postManager,
        private PostCommentManager $postCommentManager
    ) {
    }

    public function __invoke(DeleteMessage $message): void
    {
        $actor = $this->activityPubManager->findRemoteActor($message->payload['actor']);

        if (!$actor) {
            return;
        }

        if (is_array($message->payload['object'])) {
            $object = $this->apActivityrepository->findByObjectId($message->payload['object']['id']);
        } else {
            $object = $this->apActivityrepository->findByObjectId($message->payload['object']);
        }

        if (!$object) {
            return;
        }

        $object = $this->entityManager->getRepository($object['type'])->find((int) $object['id']);

        if (get_class($object) === Entry::class) {
            $fn = 'deleteEntry';
        }

        if (get_class($object) === EntryComment::class) {
            $fn = 'deleteEntryComment';
        }

        if (get_class($object) === Post::class) {
            $fn = 'deletePost';
        }

        if (get_class($object) === PostComment::class) {
            $fn = 'deletePostComment';
        }

        $this->$fn($object, $actor);
    }

    private function deleteEntry(Entry $entry, User $user)
    {
        $this->entryManager->delete($user, $entry);
    }

    private function deleteEntryComment(EntryComment $comment, User $user)
    {
        $this->entryCommentManager->delete($user, $comment);
    }

    private function deletePost(Post $post, User $user)
    {
        $this->postManager->delete($user, $post);
    }

    private function deletePostComment(PostComment $comment, User $user)
    {
        $this->postCommentManager->delete($user, $comment);
    }
}

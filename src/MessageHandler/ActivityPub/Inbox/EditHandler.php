<?php declare(strict_types=1);

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
use App\Message\ActivityPub\Inbox\DeleteMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\MarkdownConverter;
use App\Service\ActivityPubManager;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EditHandler implements MessageHandlerInterface
{
    private array $payload;

    public function __construct(
        private ActivityPubManager $activityPubManager,
        private ApActivityRepository $apActivityRepository,
        private EntityManagerInterface $entityManager,
        private EntryManager $entryManager,
        private EntryCommentManager $entryCommentManager,
        private PostManager $postManager,
        private PostCommentManager $postCommentManager,
        private MarkdownConverter $markdownConverter,
        private EntryFactory $entryFactory,
        private EntryCommentFactory $entryCommentFactory,
        private PostFactory $postFactory,
        private PostCommentFactory $postCommentFactory
    ) {
    }

    public function __invoke(DeleteMessage $message): void
    {
        $this->payload = $message->payload;

        try {
            $actor = $this->activityPubManager->findRemoteActor($message->payload['actor']);
        } catch (\Exception) {
            return;
        }

        $object = $this->apActivityRepository->findByObjectId($message->payload['object']['id']);

        if (!$object) {
            return;
        }

        $object = $this->entityManager->getRepository($object['type'])->find((int)$object['id']);

        if (get_class($object) === Entry::class) {
            $fn = 'editEntry';
        }

        if (get_class($object) === EntryComment::class) {
            $fn = 'editEntryComment';
        }

        if (get_class($object) === Post::class) {
            $fn = 'editPost';
        }

        if (get_class($object) === PostComment::class) {
            $fn = 'editPostComment';
        }

        $this->$fn($object, $actor);
    }

    private function editEntry(Entry $entry, User $user)
    {
        $dto = $this->entryFactory->createDto($entry);

        $dto->body = $this->payload['object']['name'];

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

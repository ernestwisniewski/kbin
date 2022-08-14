<?php declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\DTO\EntryCommentDto;
use App\DTO\EntryDto;
use App\DTO\PostCommentDto;
use App\DTO\PostDto;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Repository\ApActivityRepository;
use App\Repository\MagazineRepository;
use App\Repository\PostRepository;
use App\Service\ActivityPubManager;
use App\Service\EntryCommentManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class Note
{
    public function __construct(
        private ApActivityRepository $repository,
        private PostRepository $postRepository,
        private PostManager $postManager,
        private EntryCommentManager $entryCommentManager,
        private PostCommentManager $postCommentManager,
        private MagazineRepository $magazineRepository,
        private ActivityPubManager $activityPubManager,
        private EntityManagerInterface $entityManager,
        private MarkdownConverter $markdownConverter
    ) {

    }

    public function create(array $object, ?array $root = null): ActivityPubActivityInterface
    {
        $current = $this->repository->findByObjectId($object['id']);
        if ($current) {
            return $this->entityManager->getRepository($current['type'])->find((int) $current['id']);
        }

        if ($replyTo = $object['inReplyTo']) {
            // Create post or entry comment
            $parent = $this->repository->findByObjectId($replyTo);
            $parent = $this->entityManager->getRepository($parent['type'])->find((int) $parent['id']);

            $root = null;
            $fn   = null;

            if (get_class($parent) === Entry::class) {
                $root = $parent;
                $fn   = 'createEntryComment';
            }

            if (get_class($parent) === EntryComment::class) {
                $root = $parent->entry;
                $fn   = 'createEntryComment';
            }

            if (get_class($parent) === Post::class) {
                $root = $parent;
                $fn   = 'createPostComment';
            }

            if (get_class($parent) === PostComment::class) {
                $root = $parent->post;
                $fn   = 'createPostComment';
            }

            return $this->$fn($object, $parent, $root);
        }

        // Crete Post
        $existed = $this->repository->findByObjectId($object['id']);
        if ($existed) {
            return $this->postRepository->find($existed['id']);
        }

        return $this->createPost($object);
    }

    private function createEntryComment(
        array $object,
        ActivityPubActivityInterface $parent,
        ?ActivityPubActivityInterface $root = null
    ): ActivityPubActivityInterface {
        $dto = new EntryCommentDto();
        if ($parent instanceof EntryComment) {
            $dto->parent = $parent;
            $dto->root   = $parent->root ?? $parent;
        }
        $dto->entry = $root;
        $dto->body  = $this->markdownConverter->convert($object['content']);
        if (isset($object['attachment'])) {
            $dto->image = $this->activityPubManager->handleImages($object['attachment']);
        }
        $dto->apId = $object['id'];

        $this->handleDate($dto, $object['published']);

        return $this->entryCommentManager->create(
            $dto,
            $this->activityPubManager->findActorOrCreate($object['attributedTo']),
            false
        );
    }

    private function createPostComment(
        array $object,
        ActivityPubActivityInterface $parent,
        ?ActivityPubActivityInterface $root = null
    ): ActivityPubActivityInterface {
        $dto = new PostCommentDto();
        if ($parent instanceof PostComment) {
            $dto->parent = $parent;
        }
        $dto->post = $root;
        $dto->body = $this->markdownConverter->convert($object['content']);
        if (isset($object['attachment'])) {
            $dto->image = $this->activityPubManager->handleImages($object['attachment']);
        }
        $dto->apId = $object['id'];

        $this->handleDate($dto, $object['published']);

        return $this->postCommentManager->create(
            $dto,
            $this->activityPubManager->findActorOrCreate($object['attributedTo']),
            false
        );
    }

    private function createPost(
        array $object,
    ): ActivityPubActivityInterface {
        $dto           = new PostDto();
        $dto->body     = $this->markdownConverter->convert($object['content']);
        $dto->magazine = $this->magazineRepository->findOneByName('random'); // @todo magazine by tags
        if (isset($object['attachment'])) {
            $dto->image = $this->activityPubManager->handleImages($object['attachment']);
        }
        $dto->apId = $object['id'];

        $this->handleDate($dto, $object['published']);

        return $this->postManager->create(
            $dto,
            $this->activityPubManager->findActorOrCreate($object['attributedTo']),
            false
        );
    }

    private function handleDate(PostDto|PostCommentDto|EntryCommentDto|EntryDto $dto, string $date): void
    {
        $dto->createdAt  = new DateTimeImmutable($date);
        $dto->lastActive = new DateTime($date);
    }
}

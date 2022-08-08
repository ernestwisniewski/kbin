<?php declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\DTO\EntryCommentDto;
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
use League\HTMLToMarkdown\HtmlConverter;

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
    ) {

    }

    public function create(array $object, ?array $root = null): ActivityPubActivityInterface
    {
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
        $converter = new HtmlConverter(['strip_tags' => true]);
        $dto       = new EntryCommentDto();
        if ($parent instanceof EntryComment) {
            $dto->parent = $parent;
            $dto->root   = $parent->root ?? $parent;
        }
        $dto->entry = $root;
        $dto->body  = $converter->convert($object['content']);
        $dto->image = $this->activityPubManager->handleImages($object['attachment']);
        $dto->apId  = $object['id'];

        $entity = $this->entryCommentManager->create(
            $dto,
            $this->activityPubManager->findActorOrCreate($object['attributedTo'])
        );

        $this->handleDate($entity, $object['published']);

        return $entity;
    }

    private function createPostComment(
        array $object,
        ActivityPubActivityInterface $parent,
        ?ActivityPubActivityInterface $root = null
    ): ActivityPubActivityInterface {
        $converter = new HtmlConverter(['strip_tags' => true]);
        $dto       = new PostCommentDto();
        if ($parent instanceof PostComment) {
            $dto->parent = $parent;
        }
        $dto->post  = $root;
        $dto->body  = $converter->convert($object['content']);
        $dto->image = $this->activityPubManager->handleImages($object['attachment']);
        $dto->apId  = $object['id'];

        $entity = $this->postCommentManager->create(
            $dto,
            $this->activityPubManager->findActorOrCreate($object['attributedTo'])
        );

        $this->handleDate($entity, $object['published']);

        return $entity;
    }

    private function createPost(
        array $object,
    ): ActivityPubActivityInterface {
        $converter     = new HtmlConverter(['strip_tags' => true]);
        $dto           = new PostDto();
        $dto->body     = $converter->convert($object['content']);
        $dto->magazine = $this->magazineRepository->findOneByName('fediverse'); // @todo magazine by tags
        $dto->image    = $this->activityPubManager->handleImages($object['attachment']);
        $dto->apId     = $object['id'];

        $entity = $this->postManager->create(
            $dto,
            $this->activityPubManager->findActorOrCreate($object['attributedTo'])
        );

        $this->handleDate($entity, $object['published']);

        return $entity;
    }

    private function handleDate(ActivityPubActivityInterface $entity, string $date): void
    {
        $entity->createdAt  = new DateTimeImmutable($date);
        $entity->lastActive = new DateTime($date);

        $this->entityManager->flush();
    }
}

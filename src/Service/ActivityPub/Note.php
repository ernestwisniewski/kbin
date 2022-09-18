<?php declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\DTO\EntryCommentDto;
use App\DTO\EntryDto;
use App\DTO\PostCommentDto;
use App\DTO\PostDto;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
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
            return $this->entityManager->getRepository($current['type'])->find((int)$current['id']);
        }

        if (isset($object['inReplyTo']) && $replyTo = $object['inReplyTo']) {
            // Create post or entry comment
            $parent = $this->repository->findByObjectId($replyTo);
            $parent = $this->entityManager->getRepository($parent['type'])->find((int)$parent['id']);

            $root = null;
            $fn = null;

            if (get_class($parent) === Entry::class) {
                $root = $parent;
                $fn = 'createEntryComment';
            }

            if (get_class($parent) === EntryComment::class) {
                $root = $parent->entry;
                $fn = 'createEntryComment';
            }

            if (get_class($parent) === Post::class) {
                $root = $parent;
                $fn = 'createPostComment';
            }

            if (get_class($parent) === PostComment::class) {
                $root = $parent->post;
                $fn = 'createPostComment';
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
            $dto->root = $parent->root ?? $parent;
        }

        $dto->entry = $root;
        $dto->apId = $object['id'];

        if (isset($object['attachment'])) {
            $dto->image = $this->activityPubManager->handleImages($object['attachment']);

            if ($video = $this->activityPubManager->handleVideos($object['attachment'])) {
                $object['content'] .= "<br><br><a href='{$video->url}'>{$video->name}</a>";
            }
        }

        $actor = $this->activityPubManager->findActorOrCreate($object['attributedTo']);

        $dto->body = $this->markdownConverter->convert($object['content']);
        $dto->visibility = $this->getVisibility($object, $actor);
        $this->handleDate($dto, $object['published']);

        return $this->entryCommentManager->create(
            $dto,
            $actor,
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
        $dto->apId = $object['id'];

        if (isset($object['attachment'])) {
            $dto->image = $this->activityPubManager->handleImages($object['attachment']);

            if ($video = $this->activityPubManager->handleVideos($object['attachment'])) {
                $object['content'] .= "<br><br><a href='{$video->url}'>{$video->name}</a>";
            }
        }

        $actor = $this->activityPubManager->findActorOrCreate($object['attributedTo']);

        $dto->body = $this->markdownConverter->convert($object['content']);
        $dto->visibility = $this->getVisibility($object, $actor);
        $this->handleDate($dto, $object['published']);

        return $this->postCommentManager->create(
            $dto,
            $actor,
            false
        );
    }

    private function createPost(
        array $object,
    ): ActivityPubActivityInterface {
        $dto = new PostDto();
        $dto->magazine = $this->magazineRepository->findOneByName('random'); // @todo magazine by tags
        $dto->apId = $object['id'];

        if (isset($object['attachment'])) {
            $dto->image = $this->activityPubManager->handleImages($object['attachment']);

            if ($video = $this->activityPubManager->handleVideos($object['attachment'])) {
                $object['content'] .= "<br><br><a href='{$video->url}'>{$video->name}</a>";
            }
        }

        $actor = $this->activityPubManager->findActorOrCreate($object['attributedTo']);

        $dto->body = $this->markdownConverter->convert($object['content']);
        $dto->visibility = $this->getVisibility($object, $actor);
        $this->handleDate($dto, $object['published']);

        return $this->postManager->create(
            $dto,
            $actor,
            false
        );
    }

    private function handleDate(PostDto|PostCommentDto|EntryCommentDto|EntryDto $dto, string $date): void
    {
        $dto->createdAt = new DateTimeImmutable($date);
        $dto->lastActive = new DateTime($date);
    }

    private function getVisibility(array $object, User $actor): string
    {
        if (!in_array(
            ActivityPubActivityInterface::PUBLIC_URL,
            array_merge($object['to'] ?? [], $object['cc'] ?? [])
        )) {
            if (
                !in_array(
                    $actor->apFollowersUrl,
                    array_merge($object['to'] ?? [], $object['cc'] ?? [])
                )
            ) {
                throw new \Exception('PM: not implemented.');
            }

            return VisibilityInterface::VISIBILITY_PRIVATE;
        }

        return VisibilityInterface::VISIBILITY_VISIBLE;
    }
}

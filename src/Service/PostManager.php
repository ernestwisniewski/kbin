<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\PostDto;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\User;
use App\Event\Post\PostBeforeDeletedEvent;
use App\Event\Post\PostBeforePurgeEvent;
use App\Event\Post\PostCreatedEvent;
use App\Event\Post\PostDeletedEvent;
use App\Event\Post\PostEditedEvent;
use App\Event\Post\PostRestoredEvent;
use App\Exception\UserBannedException;
use App\Factory\PostFactory;
use App\Message\DeleteImageMessage;
use App\Repository\ImageRepository;
use App\Repository\PostRepository;
use App\Service\Contracts\ContentManagerInterface;
use App\Utils\Slugger;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class PostManager implements ContentManagerInterface
{
    public function __construct(
        private readonly Slugger $slugger,
        private readonly MentionManager $mentionManager,
        private readonly PostCommentManager $postCommentManager,
        private readonly TagManager $tagManager,
        private readonly PostFactory $factory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly RateLimiterFactory $postLimiter,
        private readonly MessageBusInterface $bus,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly PostRepository $postRepository,
        private readonly ImageRepository $imageRepository,
        private readonly CacheInterface $cache
    ) {
    }

    public function create(PostDto $dto, User $user, $rateLimit = true): Post
    {
        if ($rateLimit) {
            $limiter = $this->postLimiter->create($dto->ip);
            if ($limiter && false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $post = $this->factory->createFromDto($dto, $user);

        if ($dto->magazine->isBanned($user)) {
            throw new UserBannedException();
        }

        $post->lang = $dto->lang;
        $post->isAdult = $dto->isAdult || $post->magazine->isAdult;
        $post->slug = $this->slugger->slug($dto->body ?? $dto->magazine->name.' '.$dto->image->altText);
        $post->image = $dto->image ? $this->imageRepository->find($dto->image->id) : null;
        if ($post->image && !$post->image->altText) {
            $post->image->altText = $dto->imageAlt;
        }
        $post->tags = $dto->body ? $this->tagManager->extract($dto->body, $post->magazine->name) : null;
        $post->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $post->visibility = $dto->visibility;
        $post->apId = $dto->apId;
        $post->magazine->lastActive = new \DateTime();
        $post->user->lastActive = new \DateTime();
        $post->lastActive = $dto->lastActive ?? $post->lastActive;
        $post->createdAt = $dto->createdAt ?? $post->createdAt;
        if (empty($post->body) && null === $post->image) {
            throw new \Exception('Post body and image cannot be empty');
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCreatedEvent($post));

        return $post;
    }

    public function edit(Post $post, PostDto $dto): Post
    {
        Assert::same($post->magazine->getId(), $dto->magazine->getId());

        $post->body = $dto->body;
        $post->lang = $dto->lang;
        $post->isAdult = $dto->isAdult || $post->magazine->isAdult;
        $post->slug = $this->slugger->slug($dto->body ?? $dto->magazine->name.' '.$dto->image->altText);
        $oldImage = $post->image;
        if ($dto->image && $dto->image->id !== $post->image->getId()) {
            $post->image = $this->imageRepository->find($dto->image->id);
        }
        $post->tags = $dto->body ? $this->tagManager->extract($dto->body, $post->magazine->name) : null;
        $post->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $post->visibility = $dto->visibility;
        $post->editedAt = new \DateTimeImmutable('@'.time());
        if (empty($post->body) && null === $post->image) {
            throw new \Exception('Post body and image cannot be empty');
        }

        $this->entityManager->flush();

        if ($oldImage && $post->image !== $oldImage) {
            $this->bus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->dispatcher->dispatch(new PostEditedEvent($post));

        return $post;
    }

    public function delete(User $user, Post $post): void
    {
        if ($user->apDomain && $user->apDomain !== parse_url($post->apId, PHP_URL_HOST)) {
            return;
        }

        if ($post->isAuthor($user) && $post->comments->isEmpty()) {
            $this->purge($user, $post);

            return;
        }

        $this->isTrashed($user, $post) ? $post->trash() : $post->softDelete();

        $this->dispatcher->dispatch(new PostBeforeDeletedEvent($post, $user));

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostDeletedEvent($post, $user));
    }

    public function trash(User $user, Post $post): void
    {
        $post->trash();

        $this->dispatcher->dispatch(new PostBeforeDeletedEvent($post, $user));

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostDeletedEvent($post, $user));
    }

    public function purge(User $user, Post $post): void
    {
        $this->dispatcher->dispatch(new PostBeforePurgeEvent($post, $user));

        $image = $post->image?->filePath;

        $sort = new Criteria(null, ['createdAt' => Criteria::DESC]);
        foreach ($post->comments->matching($sort) as $comment) {
            $this->postCommentManager->purge($user, $comment);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        if ($image) {
            $this->bus->dispatch(new DeleteImageMessage($image));
        }
    }

    private function isTrashed(User $user, Post $post): bool
    {
        return !$post->isAuthor($user);
    }

    public function restore(User $user, Post $post): void
    {
        if (VisibilityInterface::VISIBILITY_TRASHED !== $post->visibility) {
            throw new \Exception('Invalid visibility');
        }

        $post->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostRestoredEvent($post, $user));
    }

    public function pin(Post $post): Post
    {
        $post->sticky = !$post->sticky;

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostEditedEvent($post));

        return $post;
    }

    public function createDto(Post $post): PostDto
    {
        return $this->factory->createDto($post);
    }

    public function detachImage(Post $post): void
    {
        $image = $post->image->filePath;

        $post->image = null;

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteImageMessage($image));
    }

    public function getSortRoute(string $sortBy): string
    {
        return strtolower($this->translator->trans($sortBy));
    }

    public function changeMagazine(Post $post, Magazine $magazine): void
    {
        $this->entityManager->beginTransaction();

        try {
            $oldMagazine = $post->magazine;
            $post->magazine = $magazine;

            foreach ($post->comments as $comment) {
                $comment->magazine = $magazine;
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return;
        }

        $oldMagazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($oldMagazine);
        $oldMagazine->postCount = $this->postRepository->countPostsByMagazine($oldMagazine);

        $magazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($magazine);
        $magazine->postCount = $this->postRepository->countPostsByMagazine($magazine);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostEditedEvent($post));
    }
}

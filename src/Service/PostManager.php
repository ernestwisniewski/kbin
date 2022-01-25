<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\PostDto;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Event\Post\PostBeforePurgeEvent;
use App\Event\Post\PostCreatedEvent;
use App\Event\Post\PostDeletedEvent;
use App\Event\Post\PostEditedEvent;
use App\Event\Post\PostRestoredEvent;
use App\Factory\PostFactory;
use App\Message\DeleteImageMessage;
use App\Service\Contracts\ContentManagerInterface;
use App\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Webmozart\Assert\Assert;

class  PostManager implements ContentManagerInterface
{
    public function __construct(
        private Slugger $slugger,
        private TagManager $tagManager,
        private PostFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private RateLimiterFactory $postLimiter,
        private MessageBusInterface $bus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostDto $dto, User $user): Post
    {
        $limiter = $this->postLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $post                       = $this->factory->createFromDto($dto, $user);
        $post->slug                 = $this->slugger->slug($dto->body);
        $post->image                = $dto->image;
        $post->tags                 = $this->tagManager->extract($post->body);
        $post->magazine->lastActive = new \DateTime();
        $post->magazine->addPost($post);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCreatedEvent($post));

        return $post;
    }

    public function edit(Post $post, PostDto $dto): Post
    {
        Assert::same($post->magazine->getId(), $dto->magazine->getId());

        $post->body    = $dto->body;
        $post->isAdult = $dto->isAdult;
        $oldImage      = $post->image;
        $post->image   = $dto->image;
        $post->tags    = $this->tagManager->extract($post->body);

        $this->entityManager->flush();

        if ($oldImage && $dto->image !== $oldImage) {
            $this->bus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->dispatcher->dispatch(new PostEditedEvent($post));

        return $post;
    }

    public function delete(User $user, Post $post): void
    {
        if ($post->isAuthor($user) && $post->comments->isEmpty()) {
            $this->purge($post);

            return;
        }

        $this->isTrashed($user, $post) ? $post->trash() : $post->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostDeletedEvent($post, $user));
    }

    public function restore(User $user, Post $post): void
    {
        if ($post->visibility !== VisibilityInterface::VISIBILITY_TRASHED) {
            throw new \Exception('Invalid visibility');
        }

        $post->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostRestoredEvent($post, $user));
    }

    public function purge(Post $post): void
    {
        $this->dispatcher->dispatch(new PostBeforePurgeEvent($post));

        $image = $post->image?->filePath;

        $post->magazine->removePost($post);

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
}

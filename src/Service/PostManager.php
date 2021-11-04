<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\PostDto;
use App\Entity\Post;
use App\Entity\User;
use App\Event\Post\PostBeforePurgeEvent;
use App\Event\Post\PostCreatedEvent;
use App\Event\Post\PostDeletedEvent;
use App\Event\Post\PostUpdatedEvent;
use App\Factory\PostFactory;
use App\Service\Contracts\ContentManagerInterface;
use App\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Webmozart\Assert\Assert;

class  PostManager implements ContentManagerInterface
{
    public function __construct(
        private PostFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private Slugger $slugger,
        private RateLimiterFactory $postLimiter,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostDto $dto, User $user): Post
    {
        $limiter = $this->postLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $post       = $this->factory->createFromDto($dto, $user);
        $post->slug = $this->slugger->slug($dto->body);
        $post->magazine->addPost($post);

        if ($dto->image) {
            $post->image = $dto->image;
        }

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

        if ($dto->image) {
            $post->image = $dto->image;
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostUpdatedEvent($post));

        return $post;
    }

    public function delete(User $user, Post $post): void
    {
        if ($post->isAuthor($user) && $post->comments->isEmpty()) {
            $this->purge($user, $post);

            return;
        }

        $this->isTrashed($user, $post) ? $post->trash() : $post->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostDeletedEvent($post, $user));
    }

    public function purge(User $user, Post $post): void
    {
        $this->dispatcher->dispatch(new PostBeforePurgeEvent($post));

        $post->magazine->removePost($post);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    private function isTrashed(User $user, Post $post): bool
    {
        return !$post->isAuthor($user);
    }

    public function createDto(Post $post): PostDto
    {
        return $this->factory->createDto($post);
    }
}

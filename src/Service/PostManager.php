<?php declare(strict_types=1);

namespace App\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use App\Service\Contracts\ContentManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\PostBeforePurgeEvent;
use App\Event\PostCreatedEvent;
use App\Event\PostDeletedEvent;
use App\Event\PostUpdatedEvent;
use App\Factory\PostFactory;
use Webmozart\Assert\Assert;
use App\DTO\PostDto;
use App\Entity\Post;
use App\Entity\User;

class PostManager implements ContentManager
{
    public function __construct(
        private PostFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostDto $dto, User $user): Post
    {
        $post     = $this->factory->createFromDto($dto, $user);
        $magazine = $post->magazine;

        $magazine->addPost($post);
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
            $post->setImage($dto->image);
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch((new PostUpdatedEvent($post)));

        return $post;
    }

    public function delete(Post $post, bool $trash = false): void
    {
        $trash ? $post->trash() : $post->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch((new PostDeletedEvent($post, $this->security->getUser())));
    }

    public function purge(Post $post): void
    {
        $this->dispatcher->dispatch((new PostBeforePurgeEvent($post)));

        $post->magazine->removePost($post);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function createDto(Post $post): PostDto
    {
        return $this->factory->createDto($post);
    }
}

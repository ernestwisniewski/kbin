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
        private PostFactory $postFactory,
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostDto $postDto, User $user): Post
    {
        $post     = $this->postFactory->createFromDto($postDto, $user);
        $magazine = $post->magazine;

        $magazine->addPost($post);
        if ($postDto->image) {
            $post->image = $postDto->image;
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostCreatedEvent($post));

        return $post;
    }

    public function edit(Post $post, PostDto $postDto): Post
    {
        Assert::same($post->magazine->getId(), $postDto->magazine->getId());

        $post->body    = $postDto->body;
        $post->isAdult = $postDto->isAdult;

        if ($postDto->image) {
            $post->setImage($postDto->image);
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostUpdatedEvent($post)));

        return $post;
    }

    public function delete(Post $post, bool $trash = false): void
    {
        $trash ? $post->trash() : $post->softDelete();

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostDeletedEvent($post, $this->security->getUser())));
    }

    public function purge(Post $post): void
    {
        $this->eventDispatcher->dispatch((new PostBeforePurgeEvent($post)));

        $post->magazine->removePost($post);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function createDto(Post $post): PostDto
    {
        return $this->postFactory->createDto($post);
    }
}

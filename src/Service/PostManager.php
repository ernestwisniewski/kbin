<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\PostDto;
use App\Entity\Post;
use App\Event\PostCreatedEvent;
use App\Event\PostDeletedEvent;
use App\Event\PostBeforePurgeEvent;
use App\Event\PostUpdatedEvent;
use App\Repository\PostRepository;
use App\Service\Contracts\ContentManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\PostFactory;
use Symfony\Component\Security\Core\Security;
use Webmozart\Assert\Assert;
use App\Entity\User;

class PostManager implements ContentManager
{
    public function __construct(
        private PostFactory $postFactory,
        private PostRepository $postRepository,
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostDto $postDto, User $user): Post
    {
        $post     = $this->postFactory->createFromDto($postDto, $user);
        $magazine = $post->getMagazine();

        $magazine->addPost($post);
        if ($postDto->image) {
            $post->setImage($postDto->image);
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostCreatedEvent($post));

        return $post;
    }

    public function edit(Post $post, PostDto $postDto): Post
    {
        Assert::same($post->getMagazine()->getId(), $postDto->magazine->getId());

        $post->setBody($postDto->body);
        $post->setIsAdult($postDto->isAdult);

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

        $post->getMagazine()->removePost($post);

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function createDto(Post $post): PostDto
    {
        return $this->postFactory->createDto($post);
    }
}

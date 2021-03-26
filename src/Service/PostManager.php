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
    private PostFactory $postFactory;
    private PostRepository $postRepository;
    private EventDispatcherInterface $eventDispatcher;
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PostFactory $postFactory,
        PostRepository $postRepository,
        EventDispatcherInterface $eventDispatcher,
        Security $security,
        EntityManagerInterface $entityManager
    ) {
        $this->postFactory     = $postFactory;
        $this->postRepository  = $postRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->security        = $security;
        $this->entityManager   = $entityManager;
    }

    public function create(PostDto $postDto, User $user): Post
    {
        $post     = $this->postFactory->createFromDto($postDto, $user);
        $magazine = $post->getMagazine();

        $magazine->addPost($post);
        if ($postDto->getImage()) {
            $post->setImage($postDto->getImage());
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostCreatedEvent($post));

        return $post;
    }

    public function edit(Post $post, PostDto $postDto): Post
    {
        Assert::same($post->getMagazine()->getId(), $postDto->getMagazine()->getId());

        $post->setBody($postDto->getBody());
        $post->setIsAdult($postDto->isAdult());

        if ($postDto->getImage()) {
            $post->setImage($postDto->getImage());
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostUpdatedEvent($post)));

        return $post;
    }

    public function delete(Post $post, bool $trash = false): void
    {
        if ($post->getCommentCount() >= 1) {
            $trash ? $post->trash() : $post->softDelete();
        } else {
            $this->purge($post);

            return;
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new PostDeletedEvent($post)));
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

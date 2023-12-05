<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Entity\User;
use App\Kbin\Post\DTO\PostDto;
use App\Kbin\Post\EventSubscriber\Event\PostCreatedEvent;
use App\Kbin\Post\Factory\PostFactory;
use App\Kbin\Tag\TagExtract;
use App\Repository\ImageRepository;
use App\Service\MentionManager;
use App\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

readonly class PostCreate
{
    public function __construct(
        private Slugger $slugger,
        private MentionManager $mentionManager,
        private TagExtract $tagExtract,
        private PostFactory $postFactory,
        private ImageRepository $imageRepository,
        private RateLimiterFactory $postLimiter,
        private RateLimiterFactory $spamProtectionLimiter,
        private EventDispatcherInterface $eventDispatcher,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(PostDto $dto, User $user, $rateLimit = true): Post
    {
        if ($rateLimit) {
            $limiter = $this->postLimiter->create($dto->ip);
            $spamProtection = $this->spamProtectionLimiter->create($dto->ip);
            if (false === $limiter->consume()->isAccepted() || false === $spamProtection->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $token = new UsernamePasswordToken($user, 'firewall', $user->getRoles());
        if (false === $this->accessDecisionManager->decide($token, ['create_content'], $dto->magazine)) {
            throw new AccessDeniedHttpException();
        }


        $post = $this->postFactory->createFromDto($dto, $user);

        $post->lang = $dto->lang;
        $post->isAdult = $dto->isAdult || $post->magazine->isAdult;
        $post->slug = $this->slugger->slug($dto->body ?? $dto->magazine->name.' '.$dto->image->altText);
        $post->image = $dto->image ? $this->imageRepository->find($dto->image->id) : null;
        if ($post->image && !$post->image->altText) {
            $post->image->altText = $dto->imageAlt;
        }
        $post->tags = $dto->body ? ($this->tagExtract)($dto->body, $post->magazine->name) : null;
        $post->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $post->visibility = $dto->getVisibility();
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

        $this->eventDispatcher->dispatch(new PostCreatedEvent($post));

        return $post;
    }
}

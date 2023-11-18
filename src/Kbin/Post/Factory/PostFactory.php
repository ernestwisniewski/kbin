<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post\Factory;

use App\Entity\Post;
use App\Entity\User;
use App\Factory\ImageFactory;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Kbin\Post\DTO\PostDto;
use App\Kbin\Post\DTO\PostResponseDto;
use App\Kbin\User\Factory\UserFactory;
use Symfony\Bundle\SecurityBundle\Security;

readonly class PostFactory
{
    public function __construct(
        private Security $security,
        private UserFactory $userFactory,
        private MagazineFactory $magazineFactory,
        private ImageFactory $imageFactory,
    ) {
    }

    public function createFromDto(PostDto $dto, User $user): Post
    {
        return new Post(
            $dto->body,
            $dto->magazine,
            $user,
            $dto->isAdult,
            $dto->ip
        );
    }

    public function createResponseDto(PostDto|Post $post): PostResponseDto
    {
        $dto = $post instanceof Post ? $this->createDto($post) : $post;

        return PostResponseDto::create(
            $dto->getId(),
            $this->userFactory->createSmallDto($dto->user),
            $this->magazineFactory->createSmallDto($dto->magazine),
            $dto->image,
            $dto->body,
            $dto->lang,
            $dto->isAdult,
            $dto->isPinned,
            $dto->comments,
            $dto->uv,
            $dto->dv,
            $dto->favouriteCount,
            $dto->visibility,
            $dto->tags,
            $dto->mentions,
            $dto->apId,
            $dto->createdAt,
            $dto->editedAt,
            $dto->lastActive,
            $dto->slug
        );
    }

    public function createDto(Post $post): PostDto
    {
        $dto = new PostDto();

        $dto->magazine = $post->magazine;
        $dto->user = $post->user;
        $dto->image = $post->image ? $this->imageFactory->createDto($post->image) : null;
        $dto->body = $post->body;
        $dto->lang = $post->lang;
        $dto->isAdult = $post->isAdult;
        $dto->isPinned = $post->sticky;
        $dto->slug = $post->slug;
        $dto->comments = $post->commentCount;
        $dto->uv = $post->countUpVotes();
        $dto->dv = $post->countDownVotes();
        $dto->favouriteCount = $post->favouriteCount;
        $dto->visibility = $post->getVisibility();
        $dto->createdAt = $post->createdAt;
        $dto->editedAt = $post->editedAt;
        $dto->lastActive = $post->lastActive;
        $dto->ip = $post->ip;
        $dto->tags = $post->tags;
        $dto->mentions = $post->mentions;
        $dto->apId = $post->apId;
        $dto->setId($post->getId());

        $currentUser = $this->security->getUser();
        // Only return the user's vote if permission to control voting has been given
        $dto->isFavourited = $this->security->isGranted('ROLE_OAUTH2_POST:VOTE') ? $post->isFavored($currentUser) : null;
        $dto->userVote = $this->security->isGranted('ROLE_OAUTH2_POST:VOTE') ? $post->getUserChoice($currentUser) : null;

        return $dto;
    }
}

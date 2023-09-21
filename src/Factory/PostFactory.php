<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\PostDto;
use App\DTO\PostResponseDto;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class PostFactory
{
    public function __construct(
        private readonly Security $security,
        private readonly UserFactory $userFactory,
        private readonly MagazineFactory $magazineFactory,
        private readonly ImageFactory $imageFactory,
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
        $dto->visibility = $post->visibility;
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

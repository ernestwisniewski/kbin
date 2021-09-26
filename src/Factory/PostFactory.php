<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\PostDto;
use App\Entity\Post;
use App\Entity\User;

class PostFactory
{
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

    public function createDto(Post $post): PostDto
    {
        return (new PostDto())->create(
            $post->magazine,
            $post->user,
            $post->image,
            $post->body,
            $post->isAdult,
            $post->slug,
            $post->commentCount,
            $post->countUpVotes(),
            $post->countDownVotes(),
            $post->score,
            $post->visibility,
            $post->createdAt,
            $post->ip,
            $post->getId()
        );
    }
}

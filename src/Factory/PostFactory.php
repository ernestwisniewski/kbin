<?php declare(strict_types = 1);

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
        $dto = new PostDto();

        $dto->magazine   = $post->magazine;
        $dto->user       = $post->user;
        $dto->image      = $post->image;
        $dto->body       = $post->body;
        $dto->isAdult    = $post->isAdult;
        $dto->slug       = $post->slug;
        $dto->comments   = $post->commentCount;
        $dto->uv         = $post->countUpVotes();
        $dto->dv         = $post->countDownVotes();
        $dto->score      = $post->score;
        $dto->visibility = $post->visibility;
        $dto->createdAt  = $post->createdAt;
        $dto->lastActive = $post->lastActive;
        $dto->ip         = $post->ip;
        $dto->setId($post->getId());

        return $dto;
    }
}

<?php declare(strict_types = 1);

namespace App\Factory;

use App\DTO\PostDto;
use App\Entity\Post;
use App\Entity\User;

class PostFactory
{
    public function createFromDto(PostDto $postDto, User $user): Post
    {
        return new Post(
            $postDto->getBody(),
            $postDto->getMagazine(),
            $user
        );
    }

    public function createDto(Post $post): PostDto
    {
        return (new PostDto())->create(
            $post->getMagazine(),
            $post->getBody(),
            null,
            $post->getId()
        );
    }
}

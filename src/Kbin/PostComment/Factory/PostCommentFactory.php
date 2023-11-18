<?php

declare(strict_types=1);

namespace App\Kbin\PostComment\Factory;

use App\Entity\PostComment;
use App\Entity\User;
use App\Factory\ImageFactory;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Kbin\PostComment\DTO\PostCommentDto;
use App\Kbin\PostComment\DTO\PostCommentResponseDto;
use App\Kbin\User\Factory\UserFactory;
use App\Repository\PostRepository;
use Symfony\Bundle\SecurityBundle\Security;

readonly class PostCommentFactory
{
    public function __construct(
        private Security $security,
        private UserFactory $userFactory,
        private MagazineFactory $magazineFactory,
        private ImageFactory $imageFactory,
        private PostRepository $postRepository,
    ) {
    }

    public function createFromDto(PostCommentDto $dto, User $user): PostComment
    {
        return new PostComment(
            $dto->body,
            $dto->post,
            $user,
            $dto->parent,
            $dto->ip
        );
    }

    public function createResponseDto(PostCommentDto|PostComment $comment, int $childCount = 0): PostCommentResponseDto
    {
        $dto = $comment instanceof PostComment ? $this->createDto($comment) : $comment;

        return PostCommentResponseDto::create(
            $dto->getId(),
            $this->userFactory->createSmallDto($dto->user),
            $this->magazineFactory->createSmallDto($dto->magazine),
            $this->postRepository->find($dto->post->getId()),
            $dto->parent,
            $childCount,
            $dto->image,
            $dto->body,
            $dto->lang,
            $dto->isAdult,
            $dto->uv,
            $dto->dv,
            $dto->favourites,
            $dto->visibility,
            $dto->apId,
            $dto->mentions,
            $dto->tags,
            $dto->createdAt,
            $dto->editedAt,
            $dto->lastActive
        );
    }

    public function createResponseTree(PostComment $comment, int $depth): PostCommentResponseDto
    {
        $commentDto = $this->createDto($comment);
        $toReturn = $this->createResponseDto($commentDto, array_reduce($comment->children->toArray(), PostCommentResponseDto::class.'::recursiveChildCount', 0));
        $toReturn->isFavourited = $commentDto->isFavourited;
        $toReturn->userVote = $commentDto->userVote;

        if (0 === $depth) {
            return $toReturn;
        }

        foreach ($comment->children as $childComment) {
            \assert($childComment instanceof PostComment);
            $child = $this->createResponseTree($childComment, $depth > 0 ? $depth - 1 : -1);
            array_push($toReturn->children, $child);
        }

        return $toReturn;
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        $dto = new PostCommentDto();
        $dto->magazine = $comment->magazine;
        $dto->post = $comment->post;
        $dto->user = $comment->user;
        $dto->body = $comment->body;
        $dto->lang = $comment->lang;
        $dto->image = $comment->image ? $this->imageFactory->createDto($comment->image) : null;
        $dto->isAdult = $comment->isAdult;
        $dto->uv = $comment->countUpVotes();
        $dto->dv = $comment->countDownVotes();
        $dto->favourites = $comment->favouriteCount;
        $dto->visibility = $comment->getVisibility();
        $dto->createdAt = $comment->createdAt;
        $dto->editedAt = $comment->editedAt;
        $dto->lastActive = $comment->lastActive;
        $dto->setId($comment->getId());
        $dto->parent = $comment->parent;
        $dto->mentions = $comment->mentions;
        $dto->tags = $comment->tags;

        $currentUser = $this->security->getUser();
        // Only return the user's vote if permission to control voting has been given
        $dto->isFavourited = $this->security->isGranted('ROLE_OAUTH2_POST_COMMENT:VOTE') ? $comment->isFavored($currentUser) : null;
        $dto->userVote = $this->security->isGranted('ROLE_OAUTH2_POST_COMMENT:VOTE') ? $comment->getUserChoice($currentUser) : null;

        return $dto;
    }
}

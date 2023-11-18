<?php

declare(strict_types=1);

namespace App\Kbin\EntryComment\Factory;

use App\Entity\EntryComment;
use App\Entity\User;
use App\Factory\ImageFactory;
use App\Kbin\EntryComment\DTO\EntryCommentDto;
use App\Kbin\EntryComment\DTO\EntryCommentResponseDto;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Kbin\User\Factory\UserFactory;
use Symfony\Bundle\SecurityBundle\Security;

readonly class EntryCommentFactory
{
    public function __construct(
        private Security $security,
        private ImageFactory $imageFactory,
        private UserFactory $userFactory,
        private MagazineFactory $magazineFactory,
    ) {
    }

    public function createFromDto(EntryCommentDto $dto, User $user): EntryComment
    {
        return new EntryComment(
            $dto->body,
            $dto->entry,
            $user,
            $dto->parent,
            $dto->ip
        );
    }

    public function createResponseDto(EntryCommentDto|EntryComment $comment, int $childCount = 0): EntryCommentResponseDto
    {
        $dto = $comment instanceof EntryComment ? $this->createDto($comment) : $comment;

        return EntryCommentResponseDto::create(
            $dto->getId(),
            $this->userFactory->createSmallDto($dto->user),
            $this->magazineFactory->createSmallDto($dto->magazine),
            $dto->entry->getId(),
            $dto->parent?->getId(),
            $dto->parent?->root?->getId() ?? $dto->parent?->getId(),
            $dto->image,
            $dto->body,
            $dto->lang,
            $dto->isAdult,
            $dto->uv,
            $dto->dv,
            $dto->favouriteCount,
            $dto->visibility,
            $dto->apId,
            $dto->mentions,
            $dto->tags,
            $dto->createdAt,
            $dto->editedAt,
            $dto->lastActive,
            $childCount
        );
    }

    public function createResponseTree(EntryComment $comment, int $depth = -1): EntryCommentResponseDto
    {
        $commentDto = $this->createDto($comment);
        $toReturn = $this->createResponseDto($commentDto, array_reduce($comment->children->toArray(), EntryCommentResponseDto::class.'::recursiveChildCount', 0));
        $toReturn->isFavourited = $commentDto->isFavourited;
        $toReturn->userVote = $commentDto->userVote;

        if (0 === $depth) {
            return $toReturn;
        }

        foreach ($comment->children as $childComment) {
            \assert($childComment instanceof EntryComment);
            $child = $this->createResponseTree($childComment, $depth > 0 ? $depth - 1 : -1);
            array_push($toReturn->children, $child);
        }

        return $toReturn;
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        $dto = new EntryCommentDto();
        $dto->magazine = $comment->magazine;
        $dto->entry = $comment->entry;
        $dto->user = $comment->user;
        $dto->body = $comment->body;
        $dto->lang = $comment->lang;
        $dto->parent = $comment->parent;
        $dto->isAdult = $comment->isAdult;
        $dto->image = $comment->image ? $this->imageFactory->createDto($comment->image) : null;
        $dto->visibility = $comment->getVisibility();
        $dto->uv = $comment->countUpVotes();
        $dto->dv = $comment->countDownVotes();
        $dto->favouriteCount = $comment->favouriteCount;
        $dto->mentions = $comment->mentions;
        $dto->tags = $comment->tags;
        $dto->createdAt = $comment->createdAt;
        $dto->editedAt = $comment->editedAt;
        $dto->lastActive = $comment->lastActive;
        $dto->setId($comment->getId());

        $currentUser = $this->security->getUser();
        // Only return the user's vote if permission to control voting has been given
        $dto->isFavourited = $this->security->isGranted('ROLE_OAUTH2_ENTRY_COMMENT:VOTE') ? $comment->isFavored($currentUser) : null;
        $dto->userVote = $this->security->isGranted('ROLE_OAUTH2_ENTRY_COMMENT:VOTE') ? $comment->getUserChoice($currentUser) : null;

        return $dto;
    }
}

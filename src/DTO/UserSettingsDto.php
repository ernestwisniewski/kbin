<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class UserSettingsDto implements \JsonSerializable
{
    public function __construct(
        public ?bool $notifyOnNewEntry = null,
        public ?bool $notifyOnNewEntryReply = null,
        public ?bool $notifyOnNewEntryCommentReply = null,
        public ?bool $notifyOnNewPost = null,
        public ?bool $notifyOnNewPostReply = null,
        public ?bool $notifyOnNewPostCommentReply = null,
        public ?bool $hideAdult = null,
        public ?bool $showProfileSubscriptions = null,
        public ?bool $showProfileFollowings = null,
        public ?bool $addMentionsEntries = null,
        public ?bool $addMentionsPosts = null,
        #[OA\Property(type: 'string', enum: User::HOMEPAGE_OPTIONS)]
        public ?string $homepage = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        public ?array $featuredMagazines = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        public ?array $preferredLanguages = null,
        public ?string $customCss = null
    ) {
        $this->notifyOnNewEntry = $notifyOnNewEntry;
        $this->notifyOnNewEntryReply = $notifyOnNewEntryReply;
        $this->notifyOnNewEntryCommentReply = $notifyOnNewEntryCommentReply;
        $this->notifyOnNewPost = $notifyOnNewPost;
        $this->notifyOnNewPostReply = $notifyOnNewPostReply;
        $this->notifyOnNewPostCommentReply = $notifyOnNewPostCommentReply;
        $this->hideAdult = $hideAdult;
        $this->showProfileSubscriptions = $showProfileSubscriptions;
        $this->showProfileFollowings = $showProfileFollowings;
        $this->addMentionsEntries = $addMentionsEntries;
        $this->addMentionsPosts = $addMentionsPosts;
        $this->homepage = $homepage;
        $this->featuredMagazines = $featuredMagazines;
        $this->preferredLanguages = $preferredLanguages;
        $this->customCss = $customCss;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'notifyOnNewEntry' => $this->notifyOnNewEntry,
            'notifyOnNewEntryReply' => $this->notifyOnNewEntryReply,
            'notifyOnNewEntryCommentReply' => $this->notifyOnNewEntryCommentReply,
            'notifyOnNewPost' => $this->notifyOnNewPost,
            'notifyOnNewPostReply' => $this->notifyOnNewPostReply,
            'notifyOnNewPostCommentReply' => $this->notifyOnNewPostCommentReply,
            'hideAdult' => $this->hideAdult,
            'showProfileSubscriptions' => $this->showProfileSubscriptions,
            'showProfileFollowings' => $this->showProfileFollowings,
            'addMentionsEntries' => $this->addMentionsEntries,
            'addMentionsPosts' => $this->addMentionsPosts,
            'homepage' => $this->homepage,
            'featuredMagazines' => $this->featuredMagazines,
            'preferredLanguages' => $this->preferredLanguages,
            'customCss' => $this->customCss,
        ];
    }

    public function mergeIntoDto(UserSettingsDto $dto): UserSettingsDto
    {
        $dto->notifyOnNewEntry = $this->notifyOnNewEntry ?? $dto->notifyOnNewEntry;
        $dto->notifyOnNewEntryReply = $this->notifyOnNewEntryReply ?? $dto->notifyOnNewEntryReply;
        $dto->notifyOnNewEntryCommentReply = $this->notifyOnNewEntryCommentReply ?? $dto->notifyOnNewEntryCommentReply;
        $dto->notifyOnNewPost = $this->notifyOnNewPost ?? $dto->notifyOnNewPost;
        $dto->notifyOnNewPostReply = $this->notifyOnNewPostReply ?? $dto->notifyOnNewPostReply;
        $dto->notifyOnNewPostCommentReply = $this->notifyOnNewPostCommentReply ?? $dto->notifyOnNewPostCommentReply;
        $dto->hideAdult = $this->hideAdult ?? $dto->hideAdult;
        $dto->showProfileSubscriptions = $this->showProfileSubscriptions ?? $dto->showProfileSubscriptions;
        $dto->showProfileFollowings = $this->showProfileFollowings ?? $dto->showProfileFollowings;
        $dto->addMentionsEntries = $this->addMentionsEntries ?? $dto->addMentionsEntries;
        $dto->addMentionsPosts = $this->addMentionsPosts ?? $dto->addMentionsPosts;
        $dto->homepage = $this->homepage ?? $dto->homepage;
        $dto->featuredMagazines = $this->featuredMagazines ?? $dto->featuredMagazines;
        $dto->preferredLanguages = $this->preferredLanguages ?? $dto->preferredLanguages;
        $dto->customCss = $this->customCss ?? $dto->customCss;

        return $dto;
    }
}

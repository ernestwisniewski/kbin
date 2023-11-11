<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\UserSettingsDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserSettingsManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function createDto(User $user): UserSettingsDto
    {
        return new UserSettingsDto(
            $user->notifyOnNewEntry,
            $user->notifyOnNewEntryReply,
            $user->notifyOnNewEntryCommentReply,
            $user->notifyOnNewPost,
            $user->notifyOnNewPostReply,
            $user->notifyOnNewPostCommentReply,
            $user->hideAdult,
            $user->turboMode,
            $user->showProfileSubscriptions,
            $user->showProfileFollowings,
            $user->addMentionsEntries,
            $user->addMentionsPosts,
            $user->homepage,
            $user->featuredMagazines,
            $user->preferredLanguages,
            $user->customCss,
            $user->ignoreMagazinesCustomCss
        );
    }

    public function update(User $user, UserSettingsDto $dto): void
    {
        $user->notifyOnNewEntry = $dto->notifyOnNewEntry;
        $user->notifyOnNewPost = $dto->notifyOnNewPost;
        $user->notifyOnNewPostReply = $dto->notifyOnNewPostReply;
        $user->notifyOnNewEntryCommentReply = $dto->notifyOnNewEntryCommentReply;
        $user->notifyOnNewEntryReply = $dto->notifyOnNewEntryReply;
        $user->notifyOnNewPostCommentReply = $dto->notifyOnNewPostCommentReply;
        $user->homepage = $dto->homepage;
        $user->hideAdult = $dto->hideAdult;
        $user->turboMode = $dto->turboMode;
        $user->showProfileSubscriptions = $dto->showProfileSubscriptions;
        $user->showProfileFollowings = $dto->showProfileFollowings;
        $user->addMentionsEntries = $dto->addMentionsEntries;
        $user->addMentionsPosts = $dto->addMentionsPosts;
        $user->featuredMagazines = $dto->featuredMagazines ? array_unique($dto->featuredMagazines) : null;
        $user->preferredLanguages = $dto->preferredLanguages ? array_unique($dto->preferredLanguages) : [];
        $user->customCss = $dto->customCss;
        $user->ignoreMagazinesCustomCss = $dto->ignoreMagazinesCustomCss;

        $this->entityManager->flush();
    }
}

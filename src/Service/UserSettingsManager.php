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
            $user->showProfileSubscriptions,
            $user->showProfileFollowings,
            $user->homepage,
            $user->featuredMagazines,
        );
    }

    public function update(User $user, UserSettingsDto $dto)
    {
        $user->notifyOnNewEntry = $dto->notifyOnNewEntry;
        $user->notifyOnNewPost = $dto->notifyOnNewPost;
        $user->notifyOnNewPostReply = $dto->notifyOnNewPostReply;
        $user->notifyOnNewEntryCommentReply = $dto->notifyOnNewEntryCommentReply;
        $user->notifyOnNewEntryReply = $dto->notifyOnNewEntryReply;
        $user->notifyOnNewPostCommentReply = $dto->notifyOnNewPostCommentReply;
        $user->theme = $dto->darkTheme ? User::THEME_DARK : User::THEME_LIGHT;
        $user->mode = $dto->turboMode ? User::MODE_TURBO : User::MODE_NORMAL;
        $user->homepage = $dto->homepage;
        $user->hideImages = $dto->hideImages;
        $user->hideAdult = $dto->hideAdult;
        $user->hideUserAvatars = $dto->hideUserAvatars;
        $user->hideMagazineAvatars = $dto->hideMagazineAvatars;
        $user->rightPosImages = $dto->rightPosImages;
        $user->entryPopup = $dto->entryPopup;
        $user->postPopup = $dto->postPopup;
        $user->showProfileSubscriptions = $dto->showProfileSubscriptions;
        $user->showProfileFollowings = $dto->showProfileFollowings;
        $user->featuredMagazines = $dto->featuredMagazines ? array_unique($dto->featuredMagazines) : null;

        $this->entityManager->flush();
    }
}

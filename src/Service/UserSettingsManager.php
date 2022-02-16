<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\UserSettingsDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserSettingsManager
{
    public function __construct(private EntityManagerInterface $entityManager)
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
            $user->theme === User::THEME_DARK,
            $user->mode === User::MODE_TURBO,
            $user->hideImages,
            $user->hideAdult,
            $user->rightPosImages,
            $user->showProfileSubscriptions,
            $user->showProfileFollowings,
            $user->homepage,
        );
    }

    public function update(User $user, UserSettingsDto $dto)
    {
        $user->notifyOnNewEntry             = $dto->notifyOnNewEntry;
        $user->notifyOnNewPost              = $dto->notifyOnNewPost;
        $user->notifyOnNewPostReply         = $dto->notifyOnNewPostReply;
        $user->notifyOnNewEntryCommentReply = $dto->notifyOnNewEntryCommentReply;
        $user->notifyOnNewEntryReply        = $dto->notifyOnNewEntryReply;
        $user->notifyOnNewPostCommentReply  = $dto->notifyOnNewPostCommentReply;
        $user->theme                        = $dto->darkTheme ? User::THEME_DARK : User::THEME_LIGHT;
        $user->mode                         = $dto->turboMode ? User::MODE_TURBO : User::MODE_NORMAL;
        $user->homepage                     = $dto->homepage;
        $user->hideImages                   = $dto->hideImages;
        $user->hideAdult                    = $dto->hideAdult;
        $user->rightPosImages               = $dto->rightPosImages;
        $user->showProfileSubscriptions     = $dto->showProfileSubscriptions;
        $user->showProfileFollowings        = $dto->showProfileFollowings;

        $this->entityManager->flush();
    }
}

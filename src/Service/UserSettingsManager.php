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
            $user->theme === User::THEME_DARK
        );
    }

    public function update(User $user, UserSettingsDto $dto)
    {
        $user->notifyOnNewPost              = $dto->notifyOnNewPost;
        $user->notifyOnNewEntryCommentReply = $dto->notifyOnNewEntryCommentReply;
        $user->notifyOnNewEntry             = $dto->notifyOnNewEntry;
        $user->notifyOnNewPostCommentReply  = $dto->notifyOnNewPostCommentReply;
        if ($dto->darkTheme) {
            $user->theme = User::THEME_DARK;
        } else {
            $user->theme = User::THEME_LIGHT;
        }

        $this->entityManager->flush();
    }
}

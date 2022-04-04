<?php declare(strict_types=1);

namespace App\DTO;

class UserSettingsDto
{
    public function __construct(
        public bool $notifyOnNewEntry = false,
        public bool $notifyOnNewEntryReply = false,
        public bool $notifyOnNewEntryCommentReply = true,
        public bool $notifyOnNewPost = false,
        public bool $notifyOnNewPostReply = false,
        public bool $notifyOnNewPostCommentReply = true,
        public bool $darkTheme = false,
        public bool $turboMode = false,
        public bool $hideImages = false,
        public bool $hideAdult = true,
        public bool $hideUserAvatars = true,
        public bool $hideMagazineAvatars = true,
        public bool $rightPosImages = false,
        public bool $showProfileSubscriptions = false,
        public bool $showProfileFollowings = false,
        public string $homepage = 'front_subscribed',
        public ?array $featuredMagazines = null,
    ) {
    }
}

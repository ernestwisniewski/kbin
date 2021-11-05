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
    ) {
    }
}

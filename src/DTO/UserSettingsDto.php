<?php declare(strict_types = 1);

namespace App\DTO;

class UserSettingsDto
{
    public function __construct(
        public bool $notifyOnNewEntry,
        public bool $notifyOnNewEntryReply,
        public bool $notifyOnNewEntryCommentReply,
        public bool $notifyOnNewPost,
        public bool $notifyOnNewPostReply,
        public bool $notifyOnNewPostCommentReply,
        public bool $darkTheme,
        public bool $turboMode,
    ) {
    }
}

<?php declare(strict_types=1);

namespace App\DTO;

class UserProfileSettingsDto
{
    private bool $notifyOnNewEntry;
    private bool $notifyOnNewPost;

    public function __construct(bool $notifyOnNewEntry, bool $notifyOnNewPost)
    {
        $this->notifyOnNewEntry = $notifyOnNewEntry;
        $this->notifyOnNewPost  = $notifyOnNewPost;
    }

    public function isNotifyOnNewEntry(): bool
    {
        return $this->notifyOnNewEntry;
    }

    public function setNotifyOnNewEntry(bool $notifyOnNewEntry): self
    {
        $this->notifyOnNewEntry = $notifyOnNewEntry;
        return $this;
    }

    public function isNotifyOnNewPost(): bool
    {
        return $this->notifyOnNewPost;
    }

    public function setNotifyOnNewPost(bool $notifyOnNewPost): self
    {
        $this->notifyOnNewPost = $notifyOnNewPost;
        return $this;
    }
}

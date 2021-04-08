<?php declare(strict_types=1);

namespace App\DTO;

class UserProfileSettingsDto
{
    public function __construct(public bool $notifyOnNewEntry, public bool $notifyOnNewPost)
    {
    }
}

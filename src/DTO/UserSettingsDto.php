<?php declare(strict_types=1);

namespace App\DTO;

class UserSettingsDto
{
    public function __construct(public bool $notifyOnNewEntry, public bool $notifyOnNewPost)
    {
    }
}

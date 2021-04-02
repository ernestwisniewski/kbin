<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\Magazine;
use App\Entity\User;

class MagazineBlockedEvent
{
    public function __construct(private Magazine $magazine, private User $user)
    {
    }

    public function getMagazine(): Magazine
    {
        return $this->magazine;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}

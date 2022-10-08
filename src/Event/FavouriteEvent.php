<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\User;

class FavouriteEvent
{
    public function __construct(public FavouriteInterface $subject, public User $user, public bool $removeLike = false)
    {
    }
}

<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Domain;
use App\Entity\User;

class DomainSubscribedEvent
{
    public function __construct(public Domain $domain, public User $user)
    {
    }
}

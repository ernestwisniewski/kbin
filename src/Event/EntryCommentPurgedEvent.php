<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Magazine;

class EntryCommentPurgedEvent
{
    public function __construct(public Magazine $magazine)
    {
    }
}

<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\Magazine;

class PostCommentPurgedEvent
{
    private Magazine $magazine;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;
    }

    public function getMagazine(): Magazine
    {
        return $this->magazine;
    }
}

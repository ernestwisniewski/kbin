<?php declare(strict_types = 1);

namespace App\Event\Magazine;

use App\Entity\MagazineBan;

class MagazineBanEvent
{
    public function __construct(public MagazineBan $ban)
    {

    }
}

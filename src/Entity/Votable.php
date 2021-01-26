<?php declare(strict_types = 1);

namespace App\Entity;

interface Votable
{
    const VOTE_UP = 1;
    const VOTE_NONE = 0;
    const VOTE_DOWN = -1;
}

<?php declare(strict_types=1);

namespace App\ActivityPub\Type\Extended\Actor;

use ActivityPhp\Type\Extended\Actor\Person as BasePerson;

class Person extends BasePerson
{
    protected $inbox;
}

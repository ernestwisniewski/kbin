<?php

declare(strict_types=1);

namespace App\Kbin\Magazine\DTO;

use App\Entity\Magazine;
use App\Entity\Moderator;
use App\Entity\User;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

#[Unique(Moderator::class, errorPath: 'user', fields: ['magazine', 'user'])]
class MagazineModeratorDto
{
    public ?Magazine $magazine = null;
    #[Assert\NotBlank]
    public ?User $user = null;

    public function __construct(?Magazine $magazine, User $user = null)
    {
        $this->magazine = $magazine;
        $this->user = $user;
    }
}

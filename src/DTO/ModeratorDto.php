<?php declare(strict_types = 1);

namespace App\DTO;

use App\Entity\Magazine;
use App\Entity\User;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique({"magazine", "user"}, entityClass="App\Entity\Moderator",
 *     message="Moderator istnieje", errorPath="user")
 */
class ModeratorDto
{
    public ?Magazine $magazine = null;
    #[Assert\NotBlank]
    public ?User $user = null;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;
    }
}

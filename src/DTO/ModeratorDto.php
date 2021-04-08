<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Unique;
use App\Entity\Magazine;
use App\Entity\User;

/**
 * @Unique({"magazine", "user"}, entityClass="App\Entity\Moderator",
 *     message="Moderator istnieje", errorPath="user")
 */
class ModeratorDto
{
    public Magazine $magazine;
    /**
     * @Assert\NotBlank()
     */
    public ?User $user = null;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;
    }
}

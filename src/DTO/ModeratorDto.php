<?php declare(strict_types=1);

namespace App\DTO;

use App\Validator\Unique;
use App\Entity\Magazine;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique({"magazine", "user"}, entityClass="App\Entity\Moderator",
 *     message="Moderator istnieje", errorPath="user")
 */
class ModeratorDto
{
    private Magazine $magazine;

    /**
     * @Assert\NotBlank()
     */
    private ?User $user = null;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;
    }

    public function getMagazine(): Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(Magazine $magazine): void
    {
        $this->magazine = $magazine;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}

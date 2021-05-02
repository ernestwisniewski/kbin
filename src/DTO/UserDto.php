<?php declare(strict_types=1);

namespace App\DTO;

use App\DTO\Contracts\UserDtoInterface;
use App\Entity\Image;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entityClass="App\Entity\User", errorPath="username", fields={"username"}, idFields="id")
 * @Unique(entityClass="App\Entity\User", errorPath="email", fields={"email"}, idFields="id")
 */
class UserDto implements UserDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 35)]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9_]{2,35}$/", match: true)]
    public ?string $username = null;
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;
    #[Assert\Length(min: 6, max: 4096)]
    public ?string $plainPassword = null;
    public ?Image $avatar = null;
    public ?int $id = null;
    #[Assert\IsTrue]
    public bool $agreeTerms;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function create(string $username, ?string $email = null, ?int $id = null): self
    {
        $this->id       = $id;
        $this->username = $username;
        $this->email    = $email;

        return $this;
    }
}

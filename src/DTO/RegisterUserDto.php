<?php declare(strict_types=1);

namespace App\DTO;

use App\DTO\Contracts\UserDtoInterface;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entityClass="App\Entity\User", errorPath="username", fields={"username"}, idFields="id")
 * @Unique(entityClass="App\Entity\User", errorPath="email", fields={"email"}, idFields="id")
 */
class RegisterUserDto implements UserDtoInterface
{
    /**
     * @Assert\NotBlank()
     */
    public ?string $username = null;
    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Length(
     *     min=2,
     *     max=35
     * )
     */
    public ?string $email = null;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 6,
     *     max = 4096,
     *     minMessage="Hasło powinno mieć bynajmniej {{ limit }} znaków.",
     *     maxMessage="Hasło powinno mieć nie więcej niż {{ limit }} znaków."
     * )
     */
    public ?string $plainPassword;
    /**
     * @Assert\IsTrue()
     */
    public ?bool $agreeTerms;
    private ?int $id = null;

    public function create(string $username, string $email, string $plainPassword): self
    {
        $this->username      = $username;
        $this->email         = $email;
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

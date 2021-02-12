<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Unique;

class UserDto implements UserDtoInterface
{
    private ?int $id = null;
    /**
     * @Assert\NotBlank()
     */
    private ?string $username = null;
    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Length(
     *     min=2,
     *     max=35
     * )
     */
    private ?string $email = null;
    /**
     * @Assert\Length(
     *     min = 6,
     *     max = 4096,
     *     minMessage="Hasło powinno mieć bynajmniej {{ limit }} znaków.",
     *     maxMessage="Hasło powinno mieć nie więcej niż {{ limit }} znaków."
     * )
     */
    private string $plainPassword;
    /**
     * @Assert\IsTrue()
     */
    private bool $agreeTerms;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
     */
    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return bool|null
     */
    public function getAgreeTerms(): ?bool
    {
        return $this->agreeTerms;
    }

    /**
     * @param bool|null $agreeTerms
     */
    public function setAgreeTerms(?bool $agreeTerms): void
    {
        $this->agreeTerms = $agreeTerms;
    }
}

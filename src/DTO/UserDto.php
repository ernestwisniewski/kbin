<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\DTO\Contracts\UserDtoInterface;
use App\Entity\Image;

class UserDto implements UserDtoInterface
{
    public ?int $id = null;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=35
     * )
     */
    public ?string $username = null;
    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public ?string $email = null;
    /**
     * @Assert\Length(
     *     min = 6,
     *     max = 4096,
     *     minMessage="Hasło powinno mieć bynajmniej {{ limit }} znaków.",
     *     maxMessage="Hasło powinno mieć nie więcej niż {{ limit }} znaków."
     * )
     */
    public ?string $plainPassword = null;
    public ?Image $avatar = null;
    /**
     * @Assert\IsTrue()
     */
    public bool $agreeTerms;

    public function getId(): ?int
    {
        return $this->id;
    }
}

<?php declare(strict_types=1);

namespace App\DTO;

use App\DTO\Contracts\UserDtoInterface;
use App\Entity\Image;
use App\Validator\Unique;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
    public Image|ImageDto|null $avatar = null;
    public bool $agreeTerms = false;
    public ?string $ip = null;
    public ?int $id = null;

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        if (!Request::createFromGlobals()->request->has('user_register')) {
            return;
        }

        if (false === $this->agreeTerms) {
            $this->buildViolation($context, 'agreeTerms');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function create(string $username, ?string $email = null, Image|ImageDto|null $avatar = null, ?int $id = null): self
    {
        $this->id       = $id;
        $this->username = $username;
        $this->email    = $email;
        $this->avatar   = $avatar;

        return $this;
    }

    private function buildViolation(ExecutionContextInterface $context, $path)
    {
        $context->buildViolation('This value should not be blank.')
            ->atPath($path)
            ->addViolation();
    }
}

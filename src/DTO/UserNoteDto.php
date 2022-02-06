<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class UserNoteDto
{
    #[Assert\NotBlank]
    public ?User $target;
    #[Assert\Length(min: 0, max: 255)]
    public ?string $body = null;
}

<?php declare(strict_types = 1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MessageDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 5000)]
    public ?string $body = null;
}

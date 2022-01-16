<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ContactDto
{
    #[Assert\NotBlank]
    public string $name;
    #[Assert\NotBlank]
    public string $email;
    #[Assert\NotBlank]
    public string $message;
    public ?string $ip = null;
}

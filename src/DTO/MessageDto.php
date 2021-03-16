<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MessageDto
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max="10000")
     */
    private ?string $body;

    public function getBody(): ?string {
        return $this->body;
    }

    public function setBody(?string $body): void {
        $this->body = $body;
    }
}

<?php

declare(strict_types=1);

namespace App\DTO;

class PageDto
{
    public string $body;

    public function create(string $body): self
    {
        $this->body = $body;

        return $this;
    }
}

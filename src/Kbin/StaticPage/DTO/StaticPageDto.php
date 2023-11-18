<?php

declare(strict_types=1);

namespace App\Kbin\StaticPage\DTO;

class StaticPageDto
{
    public string $title;
    public string $body;
    public string $lang = 'en';

    public function create(string $title, ?string $lang = 'en'): self
    {
        $this->title = $title;

        return $this;
    }
}

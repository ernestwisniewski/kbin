<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO\ActivityPub;

use Symfony\Component\Validator\Constraints as Assert;

class ImageDto
{
    #[Assert\NotBlank]
    public ?string $url = null;
    #[Assert\NotBlank]
    public ?string $format = null;
    public ?string $name = null;

    public function create(string $url, string $format, ?string $name): self
    {
        $this->url = $url;
        $this->format = $format;
        $this->name = $name ?? $format;

        return $this;
    }
}

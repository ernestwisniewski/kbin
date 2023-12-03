<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\StaticPage\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class StaticPageDto
{
    public ?string $title = null;
    public ?string $body = null;
    public string $lang = 'en';

    public function create(string $title, ?string $lang = 'en'): self
    {
        $this->title = $title;

        return $this;
    }
}

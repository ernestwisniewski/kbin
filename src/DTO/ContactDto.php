<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ContactDto
{
    #[Assert\NotBlank]
    public string $name;
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;
    #[Assert\NotBlank]
    public string $message;
    public ?string $ip = null;
    public ?string $surname = null; // honeypot
}

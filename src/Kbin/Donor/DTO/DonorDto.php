<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Donor\DTO;

use App\Entity\Donor;
use App\Entity\User;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

#[Unique(Donor::class, errorPath: 'email', fields: ['email'])]
class DonorDto
{
    public ?User $user = null;
    #[Assert\NotBlank]
    public ?string $email = null;
    public ?string $username = null;
    public ?string $url = null;
    public bool $isActive = false;

    public function __construct(
        ?string $email = null,
        ?string $username = null,
        ?string $url = null,
        bool $isActive = false,
        User $user = null
    ) {
        $this->email = $email;
        $this->username = $username;
        $this->url = $url;
        $this->user = $user;
        $this->isActive = $isActive;
    }
}

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Category\DTO;

use App\Entity\User;
use App\Kbin\User\DTO\UserDto;
use App\Utils\RegPatterns;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryDto
{
    public const MAX_NAME_LENGTH = 25;

    #[Assert\NotBlank]
    public ArrayCollection|null $magazines = null;
    public User|UserDto|null $user = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: self::MAX_NAME_LENGTH)]
    #[Assert\Regex(pattern: RegPatterns::MAGAZINE_NAME, match: true)]
    public ?string $name = null;
    public ?string $description = null;
    public bool $isPrivate = false;
    private ?int $id = null;
}

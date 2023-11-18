<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping\Column;

trait ActivityPubActivityTrait
{
    #[Column(type: 'string', unique: true, nullable: true)]
    public ?string $apId = null;
}

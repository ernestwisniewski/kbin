<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait UpdatedAtTrait
{
    #[ORM\Column(type: 'datetimetz_immutable')]
    public ?\DateTimeImmutable $updatedAt = null;

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): \DateTimeImmutable
    {
        $this->updatedAt = new \DateTimeImmutable('@'.time());

        return $this->updatedAt;
    }
}

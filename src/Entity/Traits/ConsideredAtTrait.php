<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ConsideredAtTrait
{
    #[ORM\Column(type: 'datetimetz_immutable', nullable: true)]
    public ?\DateTimeImmutable $consideredAt = null;

    public function getConsideredAt(): ?\DateTimeImmutable
    {
        return $this->consideredAt;
    }

    public function setConsideredAt(): \DateTimeImmutable
    {
        $this->consideredAt = new \DateTimeImmutable('@'.time());

        return $this->consideredAt;
    }
}

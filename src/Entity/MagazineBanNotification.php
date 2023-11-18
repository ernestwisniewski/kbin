<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class MagazineBanNotification extends Notification
{
    #[ManyToOne(targetEntity: MagazineBan::class, inversedBy: 'notifications')]
    #[JoinColumn]
    public ?MagazineBan $ban = null;

    public function __construct(User $receiver, MagazineBan $ban)
    {
        parent::__construct($receiver);

        $this->ban = $ban;
    }

    public function getSubject(): MagazineBan
    {
        return $this->ban;
    }

    public function getType(): string
    {
        return 'magazine_ban_notification';
    }
}

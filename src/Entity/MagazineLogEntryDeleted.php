<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class MagazineLogEntryDeleted extends MagazineLog
{
    #[ManyToOne(targetEntity: Entry::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    public ?Entry $entry = null;

    public function __construct(Entry $entry, User $user)
    {
        parent::__construct($entry->magazine, $user);

        $this->entry = $entry;
    }

    public function getType(): string
    {
        return 'log_entry_deleted';
    }

    public function getSubject(): ContentInterface
    {
        return $this->entry;
    }

    public function clearSubject(): MagazineLog
    {
        $this->entry = null;

        return $this;
    }
}

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Report\DTO;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;

class ReportDto
{
    public ?Magazine $magazine = null;
    public ?User $reported = null;
    public ?ReportInterface $subject = null;
    public ?string $reason = null;
    private ?int $id = null;

    public static function create(ReportInterface $subject, string $reason = null, int $id = null): self
    {
        $dto = new ReportDto();
        $dto->id = $id;
        $dto->subject = $subject;
        $dto->reason = $reason;

        $dto->magazine = $subject->magazine;
        $dto->reported = $subject->user;

        return $dto;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRouteName(): string
    {
        switch (\get_class($this->getSubject())) {
            case Entry::class:
                return 'entry_report';
            case EntryComment::class:
                return 'entry_comment_report';
            case Post::class:
                return 'post_report';
            case PostComment::class:
                return 'post_comment_report';
        }

        throw new \LogicException();
    }

    public function getSubject(): ReportInterface
    {
        return $this->subject;
    }
}

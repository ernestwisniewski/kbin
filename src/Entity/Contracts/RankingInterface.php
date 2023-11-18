<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Contracts;

interface RankingInterface
{
    public const DOWNVOTED_CUTOFF = -5;
    public const NETSCORE_MULTIPLIER = 4500;
    public const COMMENT_MULTIPLIER = 1500;
    public const COMMENT_UNIQUE_MULTIPLIER = 5000;
    public const COMMENT_DOWNVOTED_MULTIPLIER = 500;
    public const MAX_ADVANTAGE = 86400;
    public const MAX_PENALTY = 43200;

    public function updateRanking(): void;

    public function setRanking(int $ranking): void;

    public function getRanking(): int;

    public function getCommentCount(): int;

    public function getUniqueCommentCount(): int;

    public function getScore(): int;

    public function getCreatedAt(): \DateTimeImmutable;
}

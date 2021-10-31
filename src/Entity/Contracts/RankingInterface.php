<?php declare(strict_types = 1);

namespace App\Entity\Contracts;

use DateTimeImmutable;

interface RankingInterface
{
    const DOWNVOTED_CUTOFF = -5;
    const NETSCORE_MULTIPLIER = 1800;
    const COMMENT_MULTIPLIER = 5000;
    const COMMENT_DOWNVOTED_MULTIPLIER = 500;
    const MAX_ADVANTAGE = 86400;
    const MAX_PENALTY = 43200;

    public function updateRanking(): void;

    public function setRanking(int $ranking): void;

    public function getRanking(): int;

    public function getCommentCount(): int;

    public function getScore(): int;

    public function getCreatedAt(): DateTimeImmutable;
}

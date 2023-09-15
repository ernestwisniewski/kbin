<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait RankingTrait
{
    #[ORM\Column(type: 'integer')]
    public int $ranking = 0;

    public function updateRanking(): void
    {
        $score = $this->getScore() + \intval($this->favouriteCount * .5);
        $scoreAdvantage = $score * self::NETSCORE_MULTIPLIER;

        if ($score > self::DOWNVOTED_CUTOFF) {
            $commentAdvantage = $this->getCommentCount() * self::COMMENT_MULTIPLIER;
            $commentAdvantage += $this->getUniqueCommentCount() * self::COMMENT_UNIQUE_MULTIPLIER;
        } else {
            $commentAdvantage = $this->getCommentCount() * self::COMMENT_DOWNVOTED_MULTIPLIER;
            $commentAdvantage += $this->getUniqueCommentCount() * self::COMMENT_DOWNVOTED_MULTIPLIER;
        }

        $advantage = max(min($scoreAdvantage + $commentAdvantage, self::MAX_ADVANTAGE), -self::MAX_PENALTY);

        $this->ranking = $this->getCreatedAt()->getTimestamp() + $advantage;
    }

    public function getRanking(): int
    {
        return $this->ranking;
    }

    public function setRanking(int $ranking): void
    {
        $this->ranking = $ranking;
    }
}

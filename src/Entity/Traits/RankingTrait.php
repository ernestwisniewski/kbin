<?php declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use App\Entity\Contracts\VoteInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Vote;
use App\Entity\User;

trait RankingTrait
{
    /**
     * @ORM\Column(type="integer")
     */
    private int $ranking = 0;

    public function updateRanking(): void
    {
        $score          = $this->getScore();
        $scoreAdvantage = $score * self::NETSCORE_MULTIPLIER;

        if ($score > self::DOWNVOTED_CUTOFF) {
            $commentAdvantage = $this->getCommentCount() * self::COMMENT_MULTIPLIER;
        } else {
            $commentAdvantage = $this->getCommentCount() * self::COMMENT_DOWNVOTED_MULTIPLIER;
        }

        $advantage = max(min($scoreAdvantage + $commentAdvantage, self::MAX_ADVANTAGE), -self::MAX_PENALTY);

        $this->ranking = $this->getCreatedAt()->getTimestamp() + $advantage;
    }

    public function setRanking(int $ranking): void
    {
        $this->ranking = $ranking;
    }

    public function getRanking(): int
    {
        return $this->ranking;
    }
}

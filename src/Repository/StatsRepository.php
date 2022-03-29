<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\Site;
use App\Entity\User;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;


class StatsRepository extends ServiceEntityRepository
{
    const TYPE_GENERAL = 'general';
    const TYPE_CONTENT = 'content';
    const TYPE_VIEWS = 'views';
    const TYPE_VOTES = 'votes';

    private ?DateTime $start;
    private ?User $user;
    private ?Magazine $magazine;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }


    #[ArrayShape(['entries' => "array", 'comments' => "array", 'posts' => "array", 'replies' => "array"])]
    public function getOverallContentStats(
        User $user = null,
        Magazine $magazine = null
    ): array {
        $this->user     = $user;
        $this->magazine = $magazine;

        $entries  = $this->getMonthlyContentStats('entry');
        $comments = $this->getMonthlyContentStats('entry_comment');
        $posts    = $this->getMonthlyContentStats('post');
        $replies  = $this->getMonthlyContentStats('post_comment');

        $startDate = $this->sort(
            array_merge(
                $this->getStartDate($entries),
                $this->getStartDate($comments),
                $this->getStartDate($posts),
                $this->getStartDate($replies)
            )
        );

        if (empty($startDate)) {
            return [
                'entries'  => [],
                'comments' => [],
                'posts'    => [],
                'replies'  => [],
            ];
        }

        return [
            'entries'  => $this->prepareOverall($this->sort($entries), $startDate[0]['year'], $startDate[0]['month']),
            'comments' => $this->prepareOverall($this->sort($comments), $startDate[0]['year'], $startDate[0]['month']),
            'posts'    => $this->prepareOverall($this->sort($posts), $startDate[0]['year'], $startDate[0]['month']),
            'replies'  => $this->prepareOverall($this->sort($replies), $startDate[0]['year'], $startDate[0]['month']),
        ];
    }

    #[ArrayShape(['entries' => "array", 'comments' => "array", 'posts' => "array", 'replies' => "array"])]
    public function getContentStatsByTime(DateTime $start, ?User $user = null, ?Magazine $magazine = null): array
    {
        $this->start    = $start;
        $this->user     = $user;
        $this->magazine = $magazine;

        return [
            'entries'  => $this->prepareDaily($this->getDailyContentStats('entry')),
            'comments' => $this->prepareDaily($this->getDailyContentStats('entry_comment')),
            'posts'    => $this->prepareDaily($this->getDailyContentStats('post')),
            'replies'  => $this->prepareDaily($this->getDailyContentStats('post_comment')),
        ];
    }

    public function getOverallViewsStats(
        User $user = null,
        Magazine $magazine = null
    ): array {
        $this->user     = $user;
        $this->magazine = $magazine;

        return $this->sort($this->getMonthlyViewsStats());
    }

    public function getViewsStatsByTime(DateTime $start, ?User $user, ?Magazine $magazine): array
    {
        $this->start    = $start;
        $this->user     = $user;
        $this->magazine = $magazine;

        return $this->prepareDaily($this->getDailyViewsStats());
    }

    private function getMonthlyContentStats(string $table): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        if ($this->user) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, COUNT(e.id) as count FROM ".$table
                ." e WHERE e.user_id = ".$this->user->getId()." GROUP BY 1,2";
        } elseif ($this->magazine) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, COUNT(e.id) as count FROM ".$table
                ." e WHERE e.magazine_id = ".$this->magazine->getId()." GROUP BY 1,2";
        } else {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, COUNT(e.id) as count FROM ".$table
                ." e GROUP BY 1,2";
        }

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        return array_map(fn($val) => [
            'month' => date_parse($val['month'])['month'],
            'year'  => (int) $val['year'],
            'count' => (int) $val['count'],
        ], $stmt->fetchAllAssociative());
    }

    private function getDailyContentStats(string $type): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        if ($this->user) {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, COUNT(e.id) as count FROM ".$type." e WHERE e.created_at >= '"
                .$this->start->format('Y-m-d H:i:s')."' AND e.user_id = ".$this->user->getId()." GROUP BY 1";
        } elseif ($this->magazine) {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, COUNT(e.id) as count FROM ".$type." e WHERE e.created_at >= '"
                .$this->start->format('Y-m-d H:i:s')."' AND e.magazine_id = ".$this->magazine->getId()." GROUP BY 1";
        } else {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, COUNT(e.id) as count FROM ".$type." e WHERE e.created_at >= '"
                .$this->start->format('Y-m-d H:i:s')."' GROUP BY 1";
        }

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        $results = $stmt->fetchAllAssociative();

        usort($results, fn($a, $b): int => $a['day'] <=> $b['day']);

        return $results;
    }

    private function getMonthlyViewsStats(): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        if ($this->user) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, SUM(e.views) as count FROM entry e WHERE e.user_id = "
                .$this->user->getId()." GROUP BY 1,2";
        } elseif ($this->magazine) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, SUM(e.views) as count FROM entry e WHERE e.magazine_id = "
                .$this->magazine->getId()." GROUP BY 1,2";
        } else {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, SUM(e.views) as count FROM entry e GROUP BY 1,2";
        }

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        return array_map(fn($val) => [
            'month' => date_parse($val['month'])['month'],
            'year'  => (int) $val['year'],
            'count' => (int) $val['count'],
        ], $stmt->fetchAllAssociative());
    }

    private function getDailyViewsStats(): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        if ($this->user) {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e WHERE e.created_at >= '"
                .$this->start->format('Y-m-d H:i:s')."' AND e.user_id = ".$this->user->getId()." GROUP BY 1";
        } elseif ($this->magazine) {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e WHERE e.created_at >= '"
                .$this->start->format('Y-m-d H:i:s')."' AND e.magazine_id = ".$this->magazine->getId()." GROUP BY 1";
        } else {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e WHERE e.created_at >= '"
                .$this->start->format('Y-m-d H:i:s')."' GROUP BY 1";
        }

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        $results = $stmt->fetchAllAssociative();

        usort($results, fn($a, $b): int => $a['day'] <=> $b['day']);

        return $results;
    }

    private function sort(array $results): array
    {
        usort($results, fn($a, $b): int => [$a['year'], $a['month']]
            <=>
            [$b['year'], $b['month']]
        );

        return $results;
    }

    private function prepareOverall(array $entries, int $startYear, int $startMonth): array
    {
        $currentMonth = (int) (new \DateTime('now'))->format('n');
        $currentYear  = (int) (new \DateTime('now'))->format('Y');

        $results = [];
        for ($y = $startYear; $y <= $currentYear; $y++) {
            for ($m = 1; $m <= 12; $m++) {
                if ($y === $currentYear && $m > $currentMonth) {
                    break;
                }

                if ($y === $startYear && $m < $startMonth) {
                    continue;
                }

                $existed = array_filter($entries, fn($entry) => $entry['month'] === $m && (int) $entry['year'] === $y);

                if (!empty($existed)) {
                    $results[] = current($existed);
                    continue;
                }

                $results[] = [
                    'month' => $m,
                    'year'  => $y,
                    'count' => 0,
                ];
            }
        }

        return $results;
    }

    private function prepareDaily(array $entries): array
    {
        $to       = new DateTime();
        $interval = DateInterval::createFromDateString('1 day');
        $period   = new DatePeriod($this->start, $interval, $to);

        $results = [];
        foreach ($period as $d) {
            $existed = array_filter($entries, fn($entry) => (new DateTime($entry['day']))->format('Y-m-d') === $d->format('Y-m-d'));

            if (!empty($existed)) {
                $existed        = current($existed);
                $existed['day'] = new DateTime($existed['day']);

                $results[] = $existed;
                continue;
            }

            $results[] = [
                'day'   => $d,
                'count' => 0,
            ];
        }

        return $results;
    }

    private function getStartDate(array $values): array
    {
        return array_map(fn($val) => ['year' => $val['year'], 'month' => $val['month']], $values);
    }
}

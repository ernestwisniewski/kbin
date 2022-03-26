<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;


class StatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    #[ArrayShape(['entries' => "array", 'comments' => "array", 'posts' => "array", 'replies' => "array"])]
    public function getOverallContentStats(
        User $user = null,
        Magazine $magazine = null
    ): array {
        $entries  = $this->getStats('entry', $user, $magazine);
        $comments = $this->getStats('entry_comment', $user, $magazine);
        $posts    = $this->getStats('post', $user, $magazine);
        $replies  = $this->getStats('post_comment', $user, $magazine);

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
            'entries'  => $this->prepare($this->sort($entries), $startDate[0]['year'], $startDate[0]['month']),
            'comments' => $this->prepare($this->sort($comments), $startDate[0]['year'], $startDate[0]['month']),
            'posts'    => $this->prepare($this->sort($posts), $startDate[0]['year'], $startDate[0]['month']),
            'replies'  => $this->prepare($this->sort($replies), $startDate[0]['year'], $startDate[0]['month']),
        ];
    }

    private function getStats(string $table, User $user = null, Magazine $magazine = null): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        if ($user) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, COUNT(e.id) as count FROM ".$table
                ." e WHERE e.user_id = ".$user->getId()." GROUP BY 1,2";
        } elseif ($magazine) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, COUNT(e.id) as count FROM ".$table
                ." e WHERE e.magazine_id = ".$magazine->getId()." GROUP BY 1,2";
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

    private function sort(array $results): array
    {
        usort($results, fn($a, $b): int => [$a['year'], $a['month']]
            <=>
            [$b['year'], $b['month']]
        );

        return $results;
    }

    private function prepare(array $entries, int $startYear, int $startMonth): array
    {
        $currentMonth = (int) (new \DateTime('now'))->format('n');
        $currentYear  = (int) (new \DateTime('now'))->format('Y');

        $result = [];
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
                    $result[] = current($existed);
                    continue;
                }

                $result[] = [
                    'month' => $m,
                    'year'  => $y,
                    'count' => 0,
                ];
            }

        }

        return $result;
    }

    private function getStartDate(array $values): array
    {
        return array_map(fn($val) => ['year' => $val['year'], 'month' => $val['month']], $values);
    }
}

<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Magazine;
use App\Entity\User;
use App\Repository\StatsRepository;
use DateTime;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatsManager
{
    public function __construct(
        private StatsRepository $repository,
        private ChartBuilderInterface $chartBuilder
    ) {
    }

    public function drawMonthlyContentChart(?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->repository->getOverallContentStats($user, $magazine);

        $labels = array_map(fn($val) => ($val['month'] < 10 ? '0' : '').$val['month'].'/'.$val['year'], $stats['entries']);

        return $this->createGeneralDataset($stats, $labels);
    }

    public function drawDailyContentStatsByTime(DateTime $start, ?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->repository->getContentStatsByTime($start, $user, $magazine);

        $labels = array_map(fn($val) => $val['day']->format('Y-m-d'), $stats['entries']);

        return $this->createGeneralDataset($stats, $labels);
    }

    public function drawMonthlyViewsChart(?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats  = $this->repository->getOverallViewsStats($user, $magazine);
        $labels = array_map(fn($val) => ($val['month'] < 10 ? '0' : '').$val['month'].'/'.$val['year'], $stats);

        return $this->createViewsDataset($stats, $labels);
    }

    public function drawDailyViewsStatsByTime(DateTime $start, ?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->repository->getViewsStatsByTime($start, $user, $magazine);

        $labels = array_map(fn($val) => $val['day']->format('Y-m-d'), $stats);

        return $this->createViewsDataset($stats, $labels);
    }

    private function createGeneralDataset(array $stats, array $labels): Chart
    {
        $dataset = [
            [
                'label'       => 'Treści',
                'borderColor' => '#4382AD',
                'data'        => array_map(fn($val) => $val['count'], $stats['entries']),
            ],
            [
                'label'       => 'Komentarze',
                'borderColor' => '#6253ac',
                'data'        => array_map(fn($val) => $val['count'], $stats['comments']),
            ],
            [
                'label'       => 'Wpisy',
                'borderColor' => '#ac5353',
                'data'        => array_map(fn($val) => $val['count'], $stats['posts']),
            ],
            [
                'label'       => 'Odpowiedzi',
                'borderColor' => '#09a084',
                'data'        => array_map(fn($val) => $val['count'], $stats['replies']),
            ],
        ];

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        return $chart->setData([
            'labels'   => $labels,
            'datasets' => $dataset,
        ]);
    }

    private function createViewsDataset(array $stats, array $labels): Chart
    {
        $dataset = [
            [
                'label'       => 'Treści',
                'borderColor' => '#4382AD',
                'data'        => array_map(fn($val) => $val['count'], $stats),
            ],
        ];

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        return $chart->setData([
            'labels'   => $labels,
            'datasets' => $dataset,
        ]);
    }

    public function resolveType(?string $value, ?string $default = null): string
    {
        $routes = [
            'general' => StatsRepository::TYPE_GENERAL,
            'content' => StatsRepository::TYPE_CONTENT,
            'views'   => StatsRepository::TYPE_VIEWS,
            'votes'   => StatsRepository::TYPE_VOTES,

            'ogólne'       => StatsRepository::TYPE_GENERAL,
            'treści'       => StatsRepository::TYPE_CONTENT,
            'wyświetlenia' => StatsRepository::TYPE_VIEWS,
            'głosy'        => StatsRepository::TYPE_VOTES,
        ];

        return $routes[$value] ?? $routes[$default ?? StatsRepository::TYPE_GENERAL];
    }
}

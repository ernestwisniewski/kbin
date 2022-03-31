<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Magazine;
use App\Entity\User;
use App\Repository\StatsContentRepository;
use App\Repository\StatsRepository;
use App\Repository\StatsViewsRepository;
use App\Repository\StatsVotesRepository;
use DateTime;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatsManager
{
    public function __construct(
        private StatsViewsRepository $viewsRepository,
        private StatsVotesRepository $votesRepository,
        private StatsContentRepository $contentRepository,
        private ChartBuilderInterface $chartBuilder
    ) {
    }

    public function drawMonthlyContentChart(?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->contentRepository->getOverallStats($user, $magazine);

        $labels = array_map(fn($val) => ($val['month'] < 10 ? '0' : '').$val['month'].'/'.$val['year'], $stats['entries']);

        return $this->createGeneralDataset($stats, $labels);
    }

    public function drawDailyContentStatsByTime(DateTime $start, ?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->contentRepository->getStatsByTime($start, $user, $magazine);

        $labels = array_map(fn($val) => $val['day']->format('Y-m-d'), $stats['entries']);

        return $this->createGeneralDataset($stats, $labels);
    }

    public function drawMonthlyViewsChart(?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->viewsRepository->getOverallStats($user, $magazine);

        $labels = array_map(fn($val) => ($val['month'] < 10 ? '0' : '').$val['month'].'/'.$val['year'], $stats);

        return $this->createViewsDataset($stats, $labels);
    }

    public function drawDailyViewsStatsByTime(DateTime $start, ?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->viewsRepository->getStatsByTime($start, $user, $magazine);

        $labels = array_map(fn($val) => $val['day']->format('Y-m-d'), $stats);

        return $this->createViewsDataset($stats, $labels);
    }

    public function drawMonthlyVotesChart(?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->votesRepository->getOverallStats($user, $magazine);

        $labels = array_map(fn($val) => ($val['month'] < 10 ? '0' : '').$val['month'].'/'.$val['year'], $stats['entries']);

        return $this->createVotesDataset($stats, $labels);
    }

    public function drawDailyVotesStatsByTime(DateTime $start, ?User $user = null, ?Magazine $magazine = null): Chart
    {
        $stats = $this->votesRepository->getStatsByTime($start, $user, $magazine);

        $labels = array_map(fn($val) => $val['day']->format('Y-m-d'), $stats['entries']);

        return $this->createVotesDataset($stats, $labels);
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

    private function createVotesDataset(array $stats, array $labels): Chart
    {
        $results = [];
        foreach($stats['entries'] as $index => $entry) {
            $entry['up'] = array_sum(array_map(fn($type) => $type[$index]['up'], $stats));
            $entry['down'] = array_sum(array_map(fn($type) => $type[$index]['down'], $stats));

            $results[] = $entry;
        }

        $dataset = [
            [
                'label'       => 'Pozytywne',
                'borderColor' => '#3c5211',
                'data'        => array_map(fn($val) => $val['up'], $results),
            ],
            [
                'label'       => 'Negatywne',
                'borderColor' => '#8f0b00',
                'data'        => array_map(fn($val) => $val['down'], $results),
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

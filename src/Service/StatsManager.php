<?php declare(strict_types=1);

namespace App\Service;

use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatsManager
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder
    ) {
    }

    public function drawChart(array $stats): Chart
    {
        $labels = array_map(fn($val) => ($val['month'] <= 10 ? '0' : '').$val['month'].'/'.$val['year'], $stats['entries']);

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
}

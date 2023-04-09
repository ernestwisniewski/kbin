<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\StatsRepository;
use App\Service\InstanceStatsManager;
use App\Service\StatsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends AbstractController
{
    public function __construct(private readonly InstanceStatsManager $counter, private readonly StatsManager $manager)
    {
    }

    public function __invoke(?string $statsType, ?int $statsPeriod, Request $request): Response
    {
        $statsType = $this->manager->resolveType($statsType);

        if (!$statsPeriod) {
            $statsPeriod = 31;
        }

        if (-1 === $statsPeriod) {
            $statsPeriod = null;
        }

        if ($statsPeriod) {
            $statsPeriod = min($statsPeriod, 256);
            $start = (new \DateTime())->modify("-$statsPeriod days");
        }

        $results = match ($statsType) {
            StatsRepository::TYPE_CONTENT => $statsPeriod
                ? $this->manager->drawDailyContentStatsByTime($start)
                : $this->manager->drawMonthlyContentChart(),
            StatsRepository::TYPE_VIEWS => $statsPeriod
                ? $this->manager->drawDailyViewsStatsByTime($start)
                : $this->manager->drawMonthlyViewsChart(),
            StatsRepository::TYPE_VOTES => $statsPeriod
                ? $this->manager->drawDailyVotesStatsByTime($start)
                : $this->manager->drawMonthlyVotesChart(),
            default => null,
        };

        return $this->render(
            'stats/front.html.twig',
            [
                'type' => $statsType ?? StatsRepository::TYPE_GENERAL,
                'period' => (string)$statsPeriod,
                'chart' => $results,
            ] + ((!$statsType || StatsRepository::TYPE_GENERAL === $statsType) ? $this->counter->count() : []),
        );
    }
}

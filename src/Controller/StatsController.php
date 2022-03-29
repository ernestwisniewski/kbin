<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\StatsRepository;
use App\Service\InstanceStatsManager;
use App\Service\StatsManager;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends AbstractController
{
    public function __construct(private InstanceStatsManager $counter, private StatsManager $manager)
    {
    }

    public function __invoke(?string $statsType, ?int $statsPeriod, Request $request): Response
    {
        $statsType = $this->manager->resolveType($statsType);

        if ($statsPeriod) {
            $statsPeriod = min($statsPeriod, 256);
            $start       = (new DateTime())->modify("-$statsPeriod days");
        }

        $results = match ($statsType) {
            StatsRepository::TYPE_VIEWS => $statsPeriod
                ? $this->manager->drawDailyViewsStatsByTime($start)
                : $this->manager->drawMonthlyViewsChart(),
            StatsRepository::TYPE_VOTES => null,
            StatsRepository::TYPE_CONTENT => $statsPeriod
                ? $this->manager->drawDailyContentStatsByTime($start)
                : $this->manager->drawMonthlyContentChart(),
            default => null
        };

        return $this->render(
            'page/stats.html.twig',
            [
                'type'   => $statsType ?? StatsRepository::TYPE_GENERAL,
                'period' => $request->get('statsPeriod'),
                'chart'  => $results,
            ] + ((!$statsType || $statsType === StatsRepository::TYPE_GENERAL) ? $this->counter->count() : []),
        );
    }
}

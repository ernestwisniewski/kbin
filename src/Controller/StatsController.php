<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\StatsManager;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends AbstractController
{
    public function __construct(private StatsManager $manager)
    {
    }

    public function __invoke(?string $type, ?int $period, Request $request): Response
    {
        if ($period) {
            $period = min($period, 256);
            $start  = (new DateTime())->modify("-$period days");
        }

        return $this->render(
            'page/stats.html.twig',
            [
                'period' => $request->get('period'),
                'contentChart' => $period ? $this->manager->drawDailyStatsByTime($start) : $this->manager->drawMonthlyChart(),
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Repository\StatsRepository;
use App\Service\StatsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserStatsController extends AbstractController
{
    public function __construct(private readonly StatsManager $manager)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(?string $statsType, ?int $statsPeriod, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $statsType = $this->manager->resolveType($statsType);

        if ($statsPeriod) {
            $statsPeriod = min($statsPeriod, 256);
            $start = (new \DateTime())->modify("-$statsPeriod days");
        }

        $results = match ($statsType) {
            StatsRepository::TYPE_VIEWS => $statsPeriod
                ? $this->manager->drawDailyViewsStatsByTime($start, $this->getUserOrThrow())
                : $this->manager->drawMonthlyViewsChart($this->getUserOrThrow()),
            StatsRepository::TYPE_VOTES => $statsPeriod
                ? $this->manager->drawDailyVotesStatsByTime($start, $this->getUserOrThrow())
                : $this->manager->drawMonthlyVotesChart($this->getUserOrThrow()),
            default => $statsPeriod
                ? $this->manager->drawDailyContentStatsByTime($start, $this->getUserOrThrow())
                : $this->manager->drawMonthlyContentChart($this->getUserOrThrow())
        };

        return $this->render(
            'user/settings/stats.html.twig', [
                'user' => $this->getUserOrThrow(),
                'period' => $request->get('statsPeriod'),
                'chart' => $results,
            ]
        );
    }
}

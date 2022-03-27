<?php declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Service\StatsManager;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserStatsController extends AbstractController
{
    public function __construct(private StatsManager $manager)
    {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(?string $type, ?int $period, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        if ($period) {
            $period = min($period, 256);
            $start  = (new DateTime())->modify("-$period days");
        }

        return $this->render(
            'user/profile/front.html.twig', [
                'period' => $request->get('period'),
                'contentChart' => $period
                    ? $this->manager->drawDailyStatsByTime($start, $this->getUserOrThrow())
                    : $this->manager->drawMonthlyChart($this->getUserOrThrow()),
            ]
        );
    }
}

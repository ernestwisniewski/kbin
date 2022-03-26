<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\StatsRepository;
use App\Service\StatsManager;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends AbstractController
{
    public function __construct(private StatsRepository $repository, private StatsManager $manager)
    {
    }

    public function __invoke(): Response
    {
        return $this->render(
            'page/stats.html.twig',
            [
                'contentChart' => $this->manager->drawChart($this->repository->getOverallContentStats()),
            ]
        );
    }
}

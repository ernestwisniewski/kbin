<?php declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Repository\StatsRepository;
use App\Service\StatsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;

class MagazineStatsController extends AbstractController
{
    public function __construct(private StatsRepository $repository, private StatsManager $manager)
    {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function __invoke(Magazine $magazine): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        return $this->render(
            'magazine/panel/front.html.twig', [
                'magazine' => $magazine,
                'contentChart' => $this->manager->drawChart($this->repository->getOverallContentStats(null, $magazine)),
            ]
        );
    }
}


<?php declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Repository\StatsRepository;
use App\Service\StatsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;

class UserStatController extends AbstractController
{
    public function __construct(private StatsRepository $repository, private StatsManager $manager)
    {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        return $this->render(
            'user/profile/front.html.twig', [
                'contentChart' => $this->manager->drawChart($this->repository->getOverallContentStats($this->getUserOrThrow())),
            ]
        );
    }
}

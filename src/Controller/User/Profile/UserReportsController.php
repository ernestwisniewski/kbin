<?php declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserReportsController extends AbstractController
{
    const MODERATED = 'moderated';

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(MagazineRepository $repository, Request $request): Response
    {

        return $this->render(
            'user/profile/reports.html.twig',
            [
            ]
        );
    }
}

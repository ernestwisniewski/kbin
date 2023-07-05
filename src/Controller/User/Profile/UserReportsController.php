<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserReportsController extends AbstractController
{
    public const MODERATED = 'moderated';

    #[IsGranted('ROLE_USER')]
    public function __invoke(MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/settings/reports.html.twig',
            [
            ]
        );
    }
}

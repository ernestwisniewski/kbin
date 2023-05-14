<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminFederationController extends AbstractController
{
    public function __construct()
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke()
    {
        return $this->render(
            'admin/federation.html.twig',
            [
                'title' => 'Federation',
            ]
        );
    }
}

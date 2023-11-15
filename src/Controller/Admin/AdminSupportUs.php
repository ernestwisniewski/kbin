<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminSupportUs extends AbstractController
{
    public function __construct()
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(): Response
    {
        return new Response('');
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserReportsModController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    public function __invoke(): Response
    {
        return new Response();
    }
}

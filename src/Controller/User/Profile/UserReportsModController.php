<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;

class UserReportsModController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    public function __invoke(): Response
    {
        return new Response();
    }
}

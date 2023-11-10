<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminUserSpamProtectionController extends AbstractController
{
    public function __construct(
        private readonly UserManager $manager,
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(User $user, Request $request): Response
    {
        $this->validateCsrf('spam_protection', $request->request->get('token'));

        $this->manager->toggleSpamProtection($user);

        return $this->redirectToRefererOrHome($request);
    }
}

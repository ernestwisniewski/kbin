<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserRemoveFollowing extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_remove_following', $request->request->get('token'));

        $manager->removeFollowing($user);

        return $this->redirectToRefererOrHome($request);
    }
}

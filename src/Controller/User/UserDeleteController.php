<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDeleteController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    public function purgeContent(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_purge_content', $request->request->get('token'));

        $manager->delete($user, true, true);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function deleteContent(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_delete_content', $request->request->get('token'));

        $manager->delete($user, false, true);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function purgeAccount(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_purge_account', $request->request->get('token'));

        $manager->delete($user, true);

        return $this->redirectToRoute('front');
    }

    #[IsGranted('ROLE_ADMIN')]
    public function deleteAccount(User $user, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('user_delete_account', $request->request->get('token'));

        $manager->delete($user);

        return $this->redirectToRoute('front');
    }
}

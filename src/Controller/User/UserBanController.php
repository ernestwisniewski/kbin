<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\User\UserBan;
use App\Kbin\User\UserUnban;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserBanController extends AbstractController
{
    public function __construct(private UserBan $userBan, private UserUnban $userUnban)
    {
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    public function ban(User $user, Request $request): Response
    {
        $this->validateCsrf('user_ban', $request->request->get('token'));

        ($this->userBan)($user);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBanned' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    public function unban(User $user, Request $request): Response
    {
        $this->validateCsrf('user_ban', $request->request->get('token'));

        ($this->userUnban)($user);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBanned' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}

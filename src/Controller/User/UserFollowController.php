<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\User\UserFollow;
use App\Kbin\User\UserUnfollow;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserFollowController extends AbstractController
{
    public function __construct(private UserFollow $userFollow, private UserUnfollow $userUnfollow)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('follow', subject: 'following')]
    public function follow(User $following, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        ($this->userFollow)($this->getUserOrThrow(), $following);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($following);
        }

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('follow', subject: 'following')]
    public function unfollow(User $following, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        ($this->userUnfollow)($this->getUserOrThrow(), $following);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($following);
        }

        return $this->redirectToRefererOrHome($request);
    }

    private function getJsonResponse(User $user): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'components/_ajax.html.twig',
                    [
                        'component' => 'user_actions',
                        'attributes' => [
                            'user' => $user,
                        ],
                    ]
                ),
            ]
        );
    }
}

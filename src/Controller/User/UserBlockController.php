<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\User\UserBlock;
use App\Kbin\User\UserUnblock;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserBlockController extends AbstractController
{
    public function __construct(private readonly UserBlock $userBlock, private readonly UserUnblock $userUnblock)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function block(User $blocked, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        ($this->userBlock)($this->getUserOrThrow(), $blocked);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($blocked);
        }

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    public function unblock(User $blocked, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        ($this->userUnblock)($this->getUserOrThrow(), $blocked);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($blocked);
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

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Kbin\User\UserDeleteRequest\UserPauseAccount;
use App\Kbin\User\UserDeleteRequest\UserPauseAccountRevoke;
use App\Service\IpResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSuspendController extends AbstractController
{
    public function __construct(
        private readonly UserPauseAccount $userPauseAccount,
        private readonly UserPauseAccountRevoke $userPauseAccountRevoke,
        private readonly RateLimiterFactory $userDeleteLimiter,
        private readonly IpResolver $ipResolver
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function suspend(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $this->validateCsrf('user_suspend', $request->request->get('token'));

        $limiter = $this->userDeleteLimiter->create($this->ipResolver->resolve());
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        ($this->userPauseAccount)($this->getUserOrThrow());

        $this->addFlash('success', 'account_suspended');

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    public function reinstate(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $this->validateCsrf('user_suspend', $request->request->get('token'));

        $limiter = $this->userDeleteLimiter->create($this->ipResolver->resolve());
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        ($this->userPauseAccountRevoke)($this->getUserOrThrow());

        $this->addFlash('success', 'account_reinstated');

        return $this->redirectToRefererOrHome($request);
    }
}

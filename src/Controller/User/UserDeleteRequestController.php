<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Kbin\User\UserDeleteRequest\UserDeleteRequestCreate;
use App\Kbin\User\UserDeleteRequest\UserDeleteRequestRevoke;
use App\Service\IpResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDeleteRequestController extends AbstractController
{
    public function __construct(
        private readonly UserDeleteRequestCreate $userDeleteRequestCreate,
        private readonly UserDeleteRequestRevoke $userDeleteRequestRevoke,
        private readonly RateLimiterFactory $userDeleteLimiter,
        private readonly IpResolver $ipResolver
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function request(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $this->validateCsrf('user_delete', $request->request->get('token'));

        $limiter = $this->userDeleteLimiter->create($this->ipResolver->resolve());
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        ($this->userDeleteRequestCreate)($this->getUserOrThrow());

        $this->addFlash('success', 'delete_account_request_send');

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    public function revoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $this->validateCsrf('user_delete', $request->request->get('token'));

        $limiter = $this->userDeleteLimiter->create($this->ipResolver->resolve());
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        ($this->userDeleteRequestRevoke)($this->getUserOrThrow());

        $this->addFlash('success', 'delete_account_request_revoke');

        return $this->redirectToRefererOrHome($request);
    }
}

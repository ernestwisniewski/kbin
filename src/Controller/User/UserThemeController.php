<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Kbin\User\UserThemeToggle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserThemeController extends AbstractController
{
    public function __construct(private UserThemeToggle $userThemeToggle)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(Request $request): Response
    {
        ($this->userThemeToggle)($this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'success' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}

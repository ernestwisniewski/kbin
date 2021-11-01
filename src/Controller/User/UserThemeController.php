<?php declare(strict_types = 1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserThemeController extends AbstractController
{
    public function __invoke(UserManager $manager, Request $request): Response
    {
        $manager->toggleTheme($this->getUserOrThrow());

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

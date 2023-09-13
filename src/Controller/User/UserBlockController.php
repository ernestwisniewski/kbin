<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserBlockController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    public function block(User $blocked, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $manager->block($this->getUserOrThrow(), $blocked);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($blocked);
        }

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    public function unblock(User $blocked, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $manager->unblock($this->getUserOrThrow(), $blocked);

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

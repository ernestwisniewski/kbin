<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserFollowController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[IsGranted('follow', subject: 'following')]
    public function follow(User $following, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $manager->follow($this->getUserOrThrow(), $following);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($following);
        }

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('follow', subject: 'following')]
    public function unfollow(User $following, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $manager->unfollow($this->getUserOrThrow(), $following);

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
                        'component' => 'user_follow',
                        'attributes' => [
                            'user' => $user,
                        ],
                    ]
                ),
            ]
        );
    }
}

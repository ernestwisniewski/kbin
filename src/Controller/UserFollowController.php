<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Service\UserManager;
use App\Entity\User;

class UserFollowController extends AbstractController
{
    public function followers(User $user, UserRepository $userRepository, Request $request): Response
    {
        return $this->render(
            'user/followers.html.twig',
            [
                'user'  => $user,
                'users' => $userRepository->findFollowUsers($this->getPageNb($request), $user),
            ]
        );
    }

    public function follows(User $user, UserRepository $userRepository, Request $request): Response
    {
        return $this->render(
            'user/follows.html.twig',
            [
                'user'  => $user,
                'users' => $userRepository->findFollowedUsers($this->getPageNb($request), $user),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("follow", subject="following")
     */
    public function follow(User $following, UserManager $userManager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $userManager->follow($this->getUserOrThrow(), $following);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $following->followersCount,
                    'isSubscribed' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("follow", subject="following")
     */
    public function unfollow(User $following, UserManager $userManager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $userManager->unfollow($this->getUserOrThrow(), $following);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'subCount'     => $following->followersCount,
                    'isSubscribed' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}

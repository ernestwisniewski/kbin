<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserFollowController extends AbstractController
{
    public function followers(User $user, UserRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/followers.html.twig',
            [
                'user'  => $user,
                'users' => $repository->findFollowUsers($this->getPageNb($request), $user),
            ]
        );
    }

    public function follows(User $user, UserRepository $manager, Request $request): Response
    {
        return $this->render(
            'user/follows.html.twig',
            [
                'user'  => $user,
                'users' => $manager->findFollowedUsers($this->getPageNb($request), $user),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("follow", subject="following")
     */
    public function follow(User $following, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $manager->follow($this->getUserOrThrow(), $following);

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
    public function unfollow(User $following, UserManager $manager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $manager->unfollow($this->getUserOrThrow(), $following);

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

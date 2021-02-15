<?php

namespace App\Controller;

use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function front(): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        return $this->render(
            'profile/front.html.twig',
            [
            ]
        );
    }

    public function subMagazines(MagazineRepository $magazineRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/sub_magazines.twig',
            [
                'magazines' => $magazineRepository->findSubscribedMagazines($page, $this->getUserOrThrow()),
            ]
        );
    }

    public function subUsers(UserRepository $userRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/sub_users.twig',
            [
                'users' => $userRepository->findFollowedUsers($page, $this->getUserOrThrow()),
            ]
        );
    }

    public function blockMagazines(MagazineRepository $magazineRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/block_magazines.twig',
            [
                'magazines' => $magazineRepository->findBlockedMagazines($page, $this->getUserOrThrow()),
            ]
        );
    }

    public function blockUsers(UserRepository $userRepository, Request $request): Response
    {
        $page = (int) $request->get('strona', 1);

        return $this->render(
            'profile/block_users.twig',
            [
                'users' => $userRepository->findBlockedUsers($page, $this->getUserOrThrow()),
            ]
        );
    }
}

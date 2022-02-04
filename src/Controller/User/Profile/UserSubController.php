<?php declare(strict_types = 1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Repository\DomainRepository;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserSubController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function magazines(MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/profile/sub_magazines.html.twig',
            [
                'magazines' => $repository->findSubscribedMagazines($this->getPageNb($request), $this->getUserOrThrow()),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function users(UserRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/profile/sub_users.html.twig',
            [
                'users' => $repository->findFollowedUsers($this->getPageNb($request), $this->getUserOrThrow()),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function domains(DomainRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/profile/sub_domains.html.twig',
            [
                'domains' => $repository->findSubscribedDomains($this->getPageNb($request), $this->getUserOrThrow()),
            ]
        );
    }
}

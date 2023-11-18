<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Repository\DomainRepository;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserBlockController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    public function magazines(MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/settings/block_magazines.html.twig',
            [
                'magazines' => $repository->findBlockedMagazines($this->getPageNb($request), $this->getUserOrThrow()),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function users(UserRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/settings/block_users.html.twig',
            [
                'users' => $repository->findBlockedUsers($this->getPageNb($request), $this->getUserOrThrow()),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function domains(DomainRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/settings/block_domains.html.twig',
            [
                'domains' => $repository->findBlockedDomains($this->getPageNb($request), $this->getUserOrThrow()),
            ]
        );
    }
}

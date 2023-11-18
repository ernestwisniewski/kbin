<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\DTO\MagazineBanDto;
use App\Kbin\Magazine\Form\MagazineBanType;
use App\Kbin\Magazine\MagazineBan;
use App\Kbin\Magazine\MagazineUnban;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineBanController extends AbstractController
{
    public function __construct(
        private readonly MagazineBan $magazineBan,
        private readonly MagazineUnban $magazineUnban,
        private readonly MagazineRepository $repository,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function bans(Magazine $magazine, Request $request): Response
    {
        return $this->render(
            'magazine/panel/bans.html.twig',
            [
                'bans' => $this->repository->findBans($magazine, $this->getPageNb($request)),
                'magazine' => $magazine,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function ban(Magazine $magazine, Request $request, User $user = null): Response
    {
        if (!$user) {
            $user = $this->userRepository->findOneByUsername($request->query->get('username'));
        }

        $form = $this->createForm(MagazineBanType::class, $magazineBanDto = new MagazineBanDto());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($this->magazineBan)($magazine, $user, $this->getUserOrThrow(), $magazineBanDto);

            return $this->redirectToRoute('magazine_panel_bans', ['name' => $magazine->name]);
        }

        return $this->render(
            'magazine/panel/ban.html.twig',
            [
                'magazine' => $magazine,
                'user' => $user,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function unban(Magazine $magazine, User $user, Request $request): Response
    {
        $this->validateCsrf('magazine_unban', $request->request->get('token'));

        ($this->magazineUnban)($magazine, $user);

        return $this->redirectToRefererOrHome($request);
    }
}

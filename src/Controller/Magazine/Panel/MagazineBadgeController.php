<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Badge;
use App\Entity\Magazine;
use App\Kbin\Entry\Badge\EntryBadgeCreate;
use App\Kbin\Entry\Badge\EntryBadgeDelete;
use App\Kbin\Entry\DTO\EntryBadgeDto;
use App\Kbin\Entry\Form\EntryBadgeType;
use App\Repository\MagazineRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineBadgeController extends AbstractController
{
    public function __construct(
        private readonly MagazineRepository $repository,
        private readonly EntryBadgeCreate $entryBadgeCreate,
        private readonly EntryBadgeDelete $entryBadgeDelete
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function badges(Magazine $magazine, Request $request): Response
    {
        $badges = $this->repository->findBadges($magazine);

        $dto = new EntryBadgeDto();

        $form = $this->createForm(EntryBadgeType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto->magazine = $magazine;
            ($this->entryBadgeCreate)($dto);

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'magazine/panel/badges.html.twig',
            [
                'badges' => $badges,
                'magazine' => $magazine,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function remove(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'badge_id')]
        Badge $badge,
        Request $request
    ): Response {
        $this->validateCsrf('badge_remove', $request->request->get('token'));

        ($this->entryBadgeDelete)($badge);

        return $this->redirectToRefererOrHome($request);
    }
}

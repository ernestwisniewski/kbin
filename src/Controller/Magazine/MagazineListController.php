<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\Magazine\Form\MagazinePageViewType;
use App\Kbin\Magazine\MagazinePageView;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MagazineListController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MagazineRepository $repository,
    ) {
    }

    public function __invoke(string $sortBy, string $view, Request $request): Response
    {
        /** @var User|null $user */
        $user = $this->tokenStorage->getToken()?->getUser();

        $criteria = new MagazinePageView(
            $this->getPageNb($request),
            $sortBy,
            $this->getValueOfFederationCriteria($request),
            $user?->hideAdult ? MagazinePageView::ADULT_HIDE : MagazinePageView::ADULT_SHOW,
        );

        $form = $this->createForm(MagazinePageViewType::class, $criteria);

        $form->handleRequest($request);

        $magazines = $this->repository->findPaginated($criteria);

        return $this->render(
            'magazine/list_all.html.twig',
            [
                'form' => $form,
                'magazines' => $magazines,
                'view' => $view,
                'criteria' => $criteria,
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}

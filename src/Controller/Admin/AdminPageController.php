<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Kbin\StaticPage\Factory\StaticPageFactory;
use App\Kbin\StaticPage\Form\StaticPageType;
use App\Kbin\StaticPage\StaticPageSave;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminPageController extends AbstractController
{
    public function __construct(
        private readonly StaticPageSave $staticPageSave,
        private readonly StaticPageFactory $staticPageFactory
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request, string $page = 'about'): Response
    {
        $form = $this->createForm(StaticPageType::class, $this->staticPageFactory->createDtoFromName($page));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($this->staticPageSave)($page, $form->getData());

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render('admin/pages.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}

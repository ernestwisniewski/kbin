<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin\SupportUs;

use App\Controller\AbstractController;
use App\Kbin\StaticPage\Factory\StaticPageFactory;
use App\Kbin\StaticPage\Form\StaticPageType;
use App\Kbin\StaticPage\StaticPageSave;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminSupportUsPageController extends AbstractController
{
    public function __construct(
        private readonly StaticPageSave $staticPageSave,
        private readonly StaticPageFactory $staticPageFactory
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request, string $section = 'supportUs'): Response
    {
        $submitted = false;
        $supportUs = $this->getForm('supportUs');
        $supportUsBottom = $this->getForm('supportUsBottom');

        $$section->handleRequest($request);

        if (
            ($supportUs->isSubmitted() && $supportUs->isValid()) ||
            ($supportUsBottom->isSubmitted() && $supportUsBottom->isValid())
        ) {
            $submitted = true;
            ($this->staticPageSave)(
                $section,
                $$section->getData()
            );
        }

        return $this->render(
            'admin/support_us.html.twig',
            [
                'formGeneral' => $supportUs->createView(),
                'formBottom' => $supportUsBottom->createView(),
            ],
            new Response(
                null, $submitted ? 422 : 200
            )
        );
    }

    private function getForm(string $name): FormInterface
    {
        return $this->createForm(
            StaticPageType::class,
            $this->staticPageFactory->createDtoFromName($name)
        );
    }
}

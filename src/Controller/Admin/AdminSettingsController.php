<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Form\SettingsType;
use App\Service\SettingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminSettingsController extends AbstractController
{
    public function __construct(private readonly SettingsManager $settings)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request): Response
    {
        $dto = $this->settings->getDto();

        $form = $this->createForm(SettingsType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->settings->save($dto);

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render('admin/settings.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\SupportUs;

use App\Controller\AbstractController;
use App\Kbin\Donor\Factory\DonorFactory;
use App\Kbin\Donor\Form\DonorType;
use App\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SupportUsPageController extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly DonorFactory $donorFactory
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // handle form DonorType
        $form = $this->createForm(
            DonorType::class,
            $this->donorFactory->createDto($this->getUser()?->email, $this->getUser())
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'thanks_for_support');

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render('page/support_us.html.twig', [
            'body' => $this->pageRepository->findOneBy(['name' => 'supportUs'])?->body,
            'body_bottom' => $this->pageRepository->findOneBy(['name' => 'supportUsBottom'])?->body,
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ContactDto;
use App\Form\ContactType;
use App\Repository\SiteRepository;
use App\Service\ContactManager;
use App\Service\IpResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends AbstractController
{
    public function __invoke(
        SiteRepository $repository,
        ContactManager $manager,
        IpResolver $ipResolver,
        Request $request
    ): Response {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var ContactDto $dto
             */
            $dto = $form->getData();
            $dto->ip = $ipResolver->resolve();

            if (!$dto->surname) {
                $manager->send($dto);
            }

            $this->addFlash('success', 'email_was_sent');

            return $this->redirectToRefererOrHome($request);
        }

        $site = $repository->findAll();

        return $this->render(
            'page/contact.html.twig',
            [
                'body' => $site[0]->contact ?? '',
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}

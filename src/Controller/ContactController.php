<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\Kbin\Contact\ContactMailSend;
use App\Kbin\Contact\DTO\ContactDto;
use App\Kbin\Contact\Form\ContactType;
use App\Repository\SiteRepository;
use App\Service\IpResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends AbstractController
{
    public function __construct(
        private readonly SiteRepository $repository,
        private readonly ContactMailSend $contactMailSend,
        private readonly IpResolver $ipResolver
    ) {
    }

    public function __invoke(
        Request $request
    ): Response {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var ContactDto $dto
             */
            $dto = $form->getData();
            $dto->ip = $this->ipResolver->resolve();

            if (!$dto->surname) {
                ($this->contactMailSend)($dto);
            }

            $this->addFlash('success', 'email_was_sent');

            return $this->redirectToRefererOrHome($request);
        }

        $site = $this->repository->findAll();

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

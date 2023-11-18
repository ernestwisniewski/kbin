<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Kbin\Magazine\Form\MagazineType;
use App\Kbin\Magazine\MagazineCreate;
use App\Service\IpResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineCreateController extends AbstractController
{
    public function __construct(
        private readonly MagazineCreate $magazineCreate,
        private readonly IpResolver $ipResolver
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(MagazineType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $dto->ip = $this->ipResolver->resolve();
            $magazine = ($this->magazineCreate)($dto, $this->getUserOrThrow());

            $this->addFlash(
                'success',
                'flash_magazine_new_success'
            );

            return $this->redirectToMagazine($magazine);
        }

        return $this->render(
            'magazine/create.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\SupportUs;

use App\Controller\AbstractController;
use App\Kbin\Donor\DonorCreate;
use App\Kbin\Donor\Factory\DonorFactory;
use App\Kbin\Donor\Form\DonorType;
use App\Repository\DonorRepository;
use App\Repository\PageRepository;
use App\Service\IpResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;

class SupportUsPageController extends AbstractController
{
    public function __construct(
        private readonly DonorCreate $donorCreate,
        private readonly IpResolver $ipResolver,
        private readonly DonorFactory $donorFactory,
        private readonly PageRepository $pageRepository,
        private readonly DonorRepository $donorRepository,
        private readonly CacheInterface $cache
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $dto = $this->donorFactory->createDto($this->getUser()?->email, $this->getUser());
        $dto->ip = $this->ipResolver->resolve();

        $form = $this->createForm(DonorType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($this->donorCreate)($form->getData());

            $this->addFlash('success', 'thanks_for_support_request');

            return $this->redirectToRefererOrHome($request);
        }

        $donor = $dto->email ? $this->donorRepository->findOneByEmail($dto->email) : null;
        $donors = $this->donorRepository->findActive();
        shuffle($donors);

        return $this->render('page/support_us.html.twig', [
            'body' => $this->pageRepository->findOneBy(['name' => 'supportUs'])?->body,
            'body_middle' => $this->pageRepository->findOneBy(['name' => 'supportUsMiddle'])?->body,
            'body_bottom' => $this->pageRepository->findOneBy(['name' => 'supportUsBottom'])?->body,
            'form' => $form->createView(),
            'donor' => $donor,
            'donors' => $donors,
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}

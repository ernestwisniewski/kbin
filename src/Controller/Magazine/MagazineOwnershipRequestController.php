<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Kbin\Magazine\OwnershipRequest\MagazineOwnershipRequestAccept;
use App\Kbin\Magazine\OwnershipRequest\MagazineOwnershipRequestToggle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineOwnershipRequestController extends AbstractController
{
    public function __construct(
        private readonly MagazineOwnershipRequestToggle $magazineOwnershipRequestToggle,
        private readonly MagazineOwnershipRequestAccept $magazineOwnershipRequestAccept
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('subscribe', subject: 'magazine')]
    public function toggle(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_ownership_request', $request->request->get('token'));

        ($this->magazineOwnershipRequestToggle)($magazine, $this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function accept(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_ownership_request', $request->request->get('token'));

        ($this->magazineOwnershipRequestAccept)($magazine, $this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }
}

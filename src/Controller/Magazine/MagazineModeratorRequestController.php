<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Kbin\Magazine\ModeratorRequest\MagazineModeratorRequestToggle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineModeratorRequestController extends AbstractController
{
    public function __construct(private readonly MagazineModeratorRequestToggle $magazineModeratorRequestToggle)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('subscribe', subject: 'magazine')]
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('moderator_request', $request->request->get('token'));

        ($this->magazineModeratorRequestToggle)($magazine, $this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }
}

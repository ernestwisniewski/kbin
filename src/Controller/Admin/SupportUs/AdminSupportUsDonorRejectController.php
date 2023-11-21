<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin\SupportUs;

use App\Controller\AbstractController;
use App\Entity\Donor;
use App\Kbin\Donor\DonorDelete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminSupportUsDonorRejectController extends AbstractController
{
    public function __construct(private readonly DonorDelete $donorDelete)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Donor $donor, Request $request): Response
    {
        $this->validateCsrf('admin_pages_support_us_donor_reject', $request->request->get('token'));

        ($this->donorDelete)($donor);

        return $this->redirectToRefererOrHome($request);
    }
}

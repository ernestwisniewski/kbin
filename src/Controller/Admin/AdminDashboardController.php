<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\InstanceStatsManager;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminDashboardController extends AbstractController
{
    public function __construct(private readonly InstanceStatsManager $counter)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(?int $statsPeriod, ?bool $withFederated)
    {
        if (!$statsPeriod or -1 === $statsPeriod) {
            $statsPeriod = null;
        }

        if ($statsPeriod) {
            $statsPeriod = min($statsPeriod, 365);
        }

        if (null === $withFederated) {
            $withFederated = false;
        }

        return $this->render('admin/dashboard.html.twig', [
                'period' => $statsPeriod,
                'withFederated' => $withFederated,
            ] + $this->counter->count($statsPeriod ? "-$statsPeriod days" : null, $withFederated));
    }
}

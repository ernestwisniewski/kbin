<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\SettingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FederationController extends AbstractController
{
    public function __invoke(UserRepository $userRepository, SettingsManager $settings, Request $request): Response
    {
        if (!$settings->get('KBIN_FEDERATION_PAGE_ENABLED')) {
            return $this->redirectToRoute('front');
        }

        // list of all unique apDomains
        $apDomains = $userRepository->createQueryBuilder('u')
            ->select('u.apDomain AS apDomain')
            ->where('u.apDomain IS NOT NULL')
            ->groupBy('u.apDomain')
            ->orderBy('COUNT(u.id)', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        $allowedInstances = [];
        $defederatedInstances = $settings->get('KBIN_BANNED_INSTANCES');

        if (!empty($apDomains)) {
            $allowedInstances = array_diff($apDomains, $defederatedInstances);
        }

        return $this->render(
            'page/federation.html.twig',
            [
                'allowedInstances' => $allowedInstances,
                'defederatedInstances' => $defederatedInstances,
            ]
        );
    }
}

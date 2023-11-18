<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Repository\MagazineRepository;
use App\Repository\ReportRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserReportsController extends AbstractController
{
    public const MODERATED = 'moderated';

    public function __construct(
        private readonly ReportRepository $repository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(MagazineRepository $repository, Request $request, string $status): Response
    {
        $reports = $this->repository->findByUserPaginated($this->getUserOrThrow(), $this->getPageNb($request), status: $status);

        return $this->render(
            'user/settings/reports.html.twig',
            [
                'reports' => $reports,
            ]
        );
    }
}

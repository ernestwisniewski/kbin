<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Repository\ReportRepository;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminReportController extends AbstractController
{
    public function __construct(
        private readonly ReportRepository $repository,
    ) {
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    public function __invoke(Request $request, string $status): Response
    {
        $page = (int) $request->get('p', 1);

        $reports = $this->repository->findAllPaginated($page, $status);

        return $this->render(
            'admin/reports.html.twig',
            [
                'reports' => $reports,
            ]
        );
    }
}

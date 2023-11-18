<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use Pagerfanta\PagerfantaInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('report_list', template: 'components/report_list.html.twig')]
final class ReportListComponent
{
    public PagerfantaInterface $reports;
    public string $routeName = 'admin_reports';
    public ?string $magazineName = null;
}

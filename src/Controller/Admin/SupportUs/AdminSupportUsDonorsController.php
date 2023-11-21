<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin\SupportUs;

use App\Controller\AbstractController;
use App\Kbin\StaticPage\Factory\StaticPageFactory;
use App\Kbin\StaticPage\Form\StaticPageType;
use App\Kbin\StaticPage\StaticPageSave;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminSupportUsDonorsController extends AbstractController
{
    public function __construct(
        private readonly StaticPageSave $staticPageSave,
        private readonly StaticPageFactory $staticPageFactory
    ) {
    }


}

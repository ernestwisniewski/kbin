<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Category;
use App\Kbin\Category\CategoryOfficialToggle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminCategoryController extends AbstractController
{
    public function __construct(private CategoryOfficialToggle $categoryOfficialToggle)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function toggleOfficial(
        Category $category,
        Request $request
    ): Response {
        $this->validateCsrf('admin_category_official_toggle', $request->request->get('token'));

        ($this->categoryOfficialToggle)($category);

        return $this->redirectToRefererOrHome($request);
    }
}

<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Controller\Category;

use App\Controller\AbstractController;
use App\Entity\Category;
use App\Kbin\Category\CategorySubToggle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategorySubController extends AbstractController
{
    public function __construct(private readonly CategorySubToggle $categorySubToggle)
    {
    }

    public function subscribe(Category $category, Request $request): Response
    {
        ($this->categorySubToggle)($category, $this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }

    public function unsubscribe(Category $category, Request $request): Response
    {
        ($this->categorySubToggle)($category, $this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }
}

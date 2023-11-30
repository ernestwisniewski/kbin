<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Entity\Category;
use App\Kbin\Category\CategoryCreate;
use App\Kbin\Category\CategoryDelete;
use App\Kbin\Category\CategoryEdit;
use App\Kbin\Category\DTO\CategoryDto;
use App\Kbin\Category\Factory\CategoryFactory;
use App\Kbin\Category\Form\CategoryType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryCreate $categoryCreate,
        private readonly CategoryEdit $categoryEdit,
        private readonly CategoryDelete $categoryDelete,
        private readonly CategoryFactory $categoryFactory
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(CategoryType::class, new CategoryDto());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            ($this->categoryCreate)($form->getData(), $this->getUserOrThrow());

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'user/settings/categories.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    #[IsGranted('ROLE_USER')]
    public function edit(Category $category, Request $request): Response
    {
        $form = $this->createForm(CategoryType::class, $this->categoryFactory->createDto($category));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            ($this->categoryEdit)($form->getData(), $category);

            return $this->redirectToRoute('user_settings_categories');
        }

        return $this->render(
            'user/settings/category_edit.html.twig',
            [
                'category' => $category,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    public function delete(Category $category, Request $request): Response
    {
        $this->validateCsrf('category_delete', $request->request->get('token'));

        ($this->categoryDelete)($category);

        return $this->redirectToRoute('user_settings_categories');
    }
}

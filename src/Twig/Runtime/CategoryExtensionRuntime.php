<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\Category;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

readonly class CategoryExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private Security $security)
    {
    }

    public function isSubscribed(Category $category): bool
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $category->isSubscribed($this->security->getUser());
    }

    public function categoryUrl(Category $category, $type = 'front'): string
    {
        $params = ['username' => $category->user->username, 'category_slug' => $category->slug];

        if (!$category->isOfficial) {
            return match ($type) {
                'front' => $this->urlGenerator->generate('category_user_front', $params),
                'posts' => $this->urlGenerator->generate('category_user_posts_front', $params),
                'aggregate' => $this->urlGenerator->generate('category_user_aggregate_front', $params),
            };
        }

        $params = ['category_slug' => $category->slug];

        return match ($type) {
            'front' => $this->urlGenerator->generate('category_front', $params),
            'posts' => $this->urlGenerator->generate('category_posts_front', $params),
            'aggregate' => $this->urlGenerator->generate('category_aggregate_front', $params),
        };
    }
}

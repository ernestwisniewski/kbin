<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Tag;

use App\Controller\AbstractController;
use App\PageView\PostPageView;
use App\Repository\PostRepository;
use App\Service\TagManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagPostFrontController extends AbstractController
{
    public function __construct(private readonly TagManager $tagManager)
    {
    }

    public function __invoke(
        string $name,
        ?string $sortBy,
        ?string $time,
        PostRepository $repository,
        Request $request
    ): Response {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time))
            ->setTag($this->tagManager->transliterate(strtolower($name)));

        $posts = $repository->findByCriteria($criteria);

        return $this->render(
            'tag/posts.html.twig',
            [
                'tag' => $name,
                'posts' => $posts,
            ]
        );
    }
}

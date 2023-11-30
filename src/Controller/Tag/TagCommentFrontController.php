<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Tag;

use App\Controller\AbstractController;
use App\Kbin\EntryComment\EntryCommentPageView;
use App\Kbin\Tag\TagTransliterate;
use App\Repository\EntryCommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagCommentFrontController extends AbstractController
{
    public function __construct(
        private readonly EntryCommentRepository $repository,
        private readonly TagTransliterate $tagTransliterate
    ) {
    }

    public function __invoke(string $name, ?string $sortBy, ?string $time, Request $request): Response
    {
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time))
            ->setTag(($this->tagTransliterate)(strtolower($name)));

        $params = [
            'comments' => $this->repository->findByCriteria($criteria),
            'tag' => $name,
        ];

        return $this->render(
            'tag/comments.html.twig',
            $params
        );
    }
}

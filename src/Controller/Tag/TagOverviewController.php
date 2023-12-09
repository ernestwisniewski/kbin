<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Tag;

use App\Controller\AbstractController;
use App\Controller\User\ThemeSettingsController;
use App\Kbin\Entry\EntryPageView;
use App\Kbin\Tag\TagTransliterate;
use App\Repository\AggregateRepository;
use App\Repository\Criteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagOverviewController extends AbstractController
{
    public function __construct(
        private readonly TagTransliterate $tagTransliterate,
        private readonly AggregateRepository $aggregateRepository
    ) {
    }

    public function __invoke(string $name, ?string $sortBy, ?string $time, ?string $type, Request $request): Response
    {
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation(
                'false' === $request->cookies->get(
                    ThemeSettingsController::KBIN_FEDERATION_ENABLED,
                    true
                ) ? Criteria::AP_LOCAL : Criteria::AP_ALL
            )
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($type));
        $criteria->tag = ($this->tagTransliterate)(strtolower($name));

        $results = $this->aggregateRepository->findByCriteria($criteria);

        return $this->render(
            'tag/overview.html.twig',
            [
                'tag' => $name,
                'results' => $results,
            ]
        );
    }
}

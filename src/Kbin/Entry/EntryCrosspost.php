<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Entry;

use App\Kbin\Pagination\KbinCustomPageResultPagination;
use Pagerfanta\PagerfantaInterface;

class EntryCrosspost
{
    public function __invoke($pagination): PagerfantaInterface
    {
        $posts = $pagination->getCurrentPageResults();

        $results = $this->preparePageResults($posts);

        $pagerfanta = new KbinCustomPageResultPagination($pagination->getAdapter());
        $pagerfanta->setCurrentPage($pagination->getCurrentPage());
        $pagerfanta->setMaxNbPages($pagination->getNbPages());
        $pagerfanta->setCurrentPageResults($results);

        return $pagerfanta;
    }

    public function preparePageResults(iterable $posts): ?iterable
    {
        $firstIndexes = [];
        $tmp = [];
        $duplicates = [];

        foreach ($posts as $post) {
            $post->title = $post->title ?? $post->getId();
            $groupingField = !empty($post->url) ? $post->url : $post->title;

            if (!\in_array($groupingField, $firstIndexes)) {
                $tmp[] = $post;
                $firstIndexes[] = $groupingField;
            } else {
                if (!\in_array($groupingField, array_column($duplicates, 'groupingField'), true)) {
                    $duplicates[] = (object) [
                        'groupingField' => $groupingField,
                        'items' => [],
                    ];
                }

                $duplicateIndex = array_search($groupingField, array_column($duplicates, 'groupingField'));
                $duplicates[$duplicateIndex]->items[] = $post;

                $post->cross = true;
            }
        }

        $results = [];
        foreach ($tmp as $item) {
            $results[] = $item;
            $groupingField = !empty($item->url) ? $item->url : $item->title;

            $duplicateIndex = array_search($groupingField, array_column($duplicates, 'groupingField'));
            if (false !== $duplicateIndex) {
                foreach ($duplicates[$duplicateIndex]->items as $duplicateItem) {
                    $results[] = $duplicateItem;
                }
            }
        }

        return $results;
    }
}

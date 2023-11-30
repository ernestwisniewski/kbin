<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Controller\Category;

use App\Controller\AbstractController;
use App\Controller\User\ThemeSettingsController;
use App\Entity\Category;
use App\Kbin\Entry\EntryCrosspost;
use App\Kbin\Entry\EntryPageView;
use App\Kbin\Post\DTO\PostDto;
use App\Kbin\Post\Form\PostType;
use App\Kbin\Post\PostPageView;
use App\Repository\AggregateRepository;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use App\Repository\PostRepository;
use Pagerfanta\PagerfantaInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractController
{
    public function __construct(
        private readonly EntryCrosspost $entryCrosspost,
    ) {
    }

    public function front(
        #[MapEntity(mapping: ['category_slug' => 'slug'])]
        Category $category,
        ?string $sortBy,
        ?string $time,
        ?string $type,
        EntryRepository $entryRepository,
        Request $request
    ): Response {
        //        $request->get('_route')
        $user = $this->getUser();
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

        if (null !== $user && 0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

        $criteria->category = $category;
        $method = $criteria->resolveSort($sortBy);
        $posts = $this->$method($criteria, $entryRepository);

        $posts = ($this->entryCrosspost)($posts);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'entry/_list.html.twig',
                        [
                            'entries' => $posts,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'category/entry_front.html.twig',
            [
                'category' => $category,
                'entries' => $posts,
            ]
        );
    }

    public function posts(
        #[MapEntity(mapping: ['category_slug' => 'slug'])]
        Category $category,
        ?string $sortBy,
        ?string $time,
        PostRepository $repository,
        Request $request
    ): Response {
        $user = $this->getUser();

        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation(
                'false' === $request->cookies->get(
                    ThemeSettingsController::KBIN_FEDERATION_ENABLED,
                    true
                ) ? Criteria::AP_LOCAL : Criteria::AP_ALL
            )
            ->setTime($criteria->resolveTime($time));

        if (null !== $user && 0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

        $criteria->category = $category;

        $posts = $repository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'post/_list.html.twig',
                        [
                            'posts' => $posts,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'category/post_front.html.twig',
            [
                'category' => $category,
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->setData(new PostDto())->createView(),
            ]
        );
    }

    public function aggregate(
        #[MapEntity(mapping: ['category_slug' => 'slug'])]
        Category $category,
        ?string $sortBy,
        ?string $time,
        ?string $type,
        AggregateRepository $aggregateRepository,
        Request $request
    ): Response {
        $user = $this->getUser();

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

        if (null !== $user && 0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

        $method = $criteria->resolveSort($sortBy);
        $criteria->category = $category;

        $posts = $this->$method($criteria, $aggregateRepository);

        $posts = ($this->entryCrosspost)($posts);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'aggregate/_list.html.twig',
                        [
                            'results' => $posts,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'category/aggregate_front.html.twig',
            [
                'category' => $category,
                'results' => $posts,
            ]
        );
    }

    private function hot(EntryPageView $criteria, $repository): PagerfantaInterface
    {
        return $repository->findByCriteria($criteria->showSortOption(Criteria::SORT_HOT));
    }

    private function top(EntryPageView $criteria, $repository): PagerfantaInterface
    {
        return $repository->findByCriteria($criteria->showSortOption(Criteria::SORT_TOP));
    }

    private function active(EntryPageView $criteria, $repository): PagerfantaInterface
    {
        return $repository->findByCriteria($criteria->showSortOption(Criteria::SORT_ACTIVE));
    }

    private function newest(EntryPageView $criteria, $repository): PagerfantaInterface
    {
        return $repository->findByCriteria($criteria->showSortOption(Criteria::SORT_NEW));
    }

    private function oldest(EntryPageView $criteria, $repository): PagerfantaInterface
    {
        return $repository->findByCriteria($criteria->showSortOption(Criteria::SORT_OLD));
    }

    private function commented(EntryPageView $criteria, $repository): PagerfantaInterface
    {
        return $repository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }
}

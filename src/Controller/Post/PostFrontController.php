<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Controller\User\ThemeSettingsController;
use App\Entity\Magazine;
use App\Kbin\Post\DTO\PostDto;
use App\Kbin\Post\Form\PostType;
use App\Kbin\Post\PostPageView;
use App\Repository\Criteria;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostFrontController extends AbstractController
{
    public function front(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $user = $this->getUser();

        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));

        if (null !== $user && 0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

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
            'post/front.html.twig',
            [
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->setData(new PostDto())->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function subscribed(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $user = $this->getUserOrThrow();

        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));
        $criteria->subscribed = true;

        if (0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

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
            'post/front.html.twig',
            [
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->setData(new PostDto())->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function moderated(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));
        $criteria->moderated = true;

        // No language criteria for moderated view

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
            'post/front.html.twig',
            [
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->setData(new PostDto())->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function favourite(?string $sortBy, ?string $time, PostRepository $repository, Request $request): Response
    {
        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));
        $criteria->favourite = true;

        // No language criteria for favourited view

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
            'post/front.html.twig',
            [
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->setData(new PostDto())->createView(),
            ]
        );
    }

    public function magazine(
        Magazine $magazine,
        ?string $sortBy,
        ?string $time,
        PostRepository $repository,
        Request $request
    ): Response {
        $user = $this->getUser();
        $response = new Response();
        if ($magazine->apId) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        $criteria = new PostPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));
        $criteria->magazine = $magazine;
        $criteria->stickiesFirst = true;

        if (null !== $user && 0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

        $posts = $repository->findByCriteria($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'post/_list.html.twig',
                        [
                            'magazine' => $magazine,
                            'posts' => $posts,
                        ]
                    ),
                ]
            );
        }

        $dto = new PostDto();
        $dto->magazine = $magazine;

        return $this->render(
            'post/front.html.twig',
            [
                'magazine' => $magazine,
                'posts' => $posts,
                'form' => $this->createForm(PostType::class)->setData($dto)->createView(),
            ],
            $response
        );
    }
}

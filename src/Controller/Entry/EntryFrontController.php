<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Controller\User\ThemeSettingsController;
use App\Entity\Magazine;
use App\Entity\User;
use App\PageView\EntryPageView;
use App\Pagination\Pagerfanta as KbinPagerfanta;
use App\Repository\Criteria;
use App\Repository\EntryRepository;
use Pagerfanta\PagerfantaInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryFrontController extends AbstractController
{
    public function __construct(private readonly EntryRepository $repository)
    {
    }

    public function root(?string $sortBy, ?string $time, ?string $type, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->front($sortBy, $time, $type, $request);
        }

        $front = match ($user->homepage) {
            User::HOMEPAGE_SUB => 'subscribed',
            User::HOMEPAGE_MOD => 'moderated',
            User::HOMEPAGE_FAV => 'favourite',
            default => 'front',
        };

        return $this->$front($sortBy, $time, $type, $request);
    }

    public function front(?string $sortBy, ?string $time, ?string $type, Request $request): Response
    {
        $user = $this->getUser();
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($type));

        if (null !== $user && 0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

        $method = $criteria->resolveSort($sortBy);
        $posts = $this->$method($criteria);

        $posts = $this->handleCrossposts($posts);

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
            'entry/front.html.twig',
            [
                'entries' => $posts,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function subscribed(?string $sortBy, ?string $time, ?string $type, Request $request): Response
    {
        $user = $this->getUserOrThrow();

        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($type));
        $criteria->subscribed = true;

        if (0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

        $method = $criteria->resolveSort($sortBy);
        $listing = $this->$method($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'entry/_list.html.twig',
                        [
                            'entries' => $listing,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'entry/front.html.twig',
            [
                'entries' => $listing,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function moderated(?string $sortBy, ?string $time, ?string $type, Request $request): Response
    {
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($type));
        $criteria->moderated = true;

        // We do not set language filter for moderated view.

        $method = $criteria->resolveSort($sortBy);
        $listing = $this->$method($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'entry/_list.html.twig',
                        [
                            'entries' => $listing,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'entry/front.html.twig',
            [
                'entries' => $listing,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    public function favourite(?string $sortBy, ?string $time, ?string $type, Request $request): Response
    {
        $criteria = new EntryPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($type));
        $criteria->favourite = true;

        // No language criteria for favourites, either

        $method = $criteria->resolveSort($sortBy);
        $listing = $this->$method($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'entry/_list.html.twig',
                        [
                            'entries' => $listing,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'entry/front.html.twig',
            [
                'entries' => $listing,
            ]
        );
    }

    public function magazine(
        #[MapEntity(expr: 'repository.findOneByName(name)')]
        Magazine $magazine,
        ?string $sortBy,
        ?string $time,
        ?string $type,
        Request $request
    ): Response {
        $user = $this->getUser();
        $response = new Response();
        if ($magazine->apId) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        $criteria = (new EntryPageView($this->getPageNb($request)));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time))
            ->setType($criteria->resolveType($type));
        $criteria->magazine = $magazine;
        $criteria->stickiesFirst = true;

        if (null !== $user && 0 < \count($user->preferredLanguages)) {
            $criteria->languages = $user->preferredLanguages;
        }

        $method = $criteria->resolveSort($sortBy);
        $listing = $this->$method($criteria);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'html' => $this->renderView(
                        'entry/_list.html.twig',
                        [
                            'magazine' => $magazine,
                            'entries' => $listing,
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'entry/front.html.twig',
            [
                'magazine' => $magazine,
                'entries' => $listing,
            ],
            $response
        );
    }

    private function hot(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_HOT));
    }

    private function top(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_TOP));
    }

    private function active(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_ACTIVE));
    }

    private function newest(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_NEW));
    }

    private function oldest(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_OLD));
    }

    private function commented(EntryPageView $criteria): PagerfantaInterface
    {
        return $this->repository->findByCriteria($criteria->showSortOption(Criteria::SORT_COMMENTED));
    }

    private function handleCrossposts($pagination): PagerfantaInterface
    {
        $posts = $pagination->getCurrentPageResults();

        $firstIndexes = [];
        $results = [];

        foreach ($posts as $item) {
            $groupingField = !empty($item->url) ? $item->url : $item->title;
            if (!\in_array($groupingField, $firstIndexes)) {
                $results[] = $item;
                $firstIndexes[] = $groupingField;
            } else {
                $insertIndex = array_search($groupingField, array_column($results, 'url')) + 1;
                array_splice($results, $insertIndex, 0, [$item]);
                $results[$insertIndex]->cross = true;
            }
        }

        $pagerfanta = new KbinPagerfanta($pagination->getAdapter());
        $pagerfanta->setCurrentPageResults($results);

        return $pagerfanta;
    }
}

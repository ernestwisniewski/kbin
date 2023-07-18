<?php

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Controller\User\ThemeSettingsController;
use App\Entity\Magazine;
use App\PageView\EntryCommentPageView;
use App\Repository\Criteria;
use App\Repository\EntryCommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryCommentFrontController extends AbstractController
{
    public function __construct(
        private readonly EntryCommentRepository $repository,
    ) {
    }

    public function front(?Magazine $magazine, ?string $sortBy, ?string $time, Request $request): Response
    {
        $params = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy ?? Criteria::SORT_DEFAULT))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));

        if ($magazine) {
            $criteria->magazine = $params['magazine'] = $magazine;
        }

        $params['comments'] = $this->repository->findByCriteria($criteria);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    #[IsGranted('ROLE_USER')]
    public function subscribed(?string $sortBy, ?string $time, Request $request): Response
    {
        $params = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));
        $criteria->subscribed = true;

        $params['comments'] = $this->repository->findByCriteria($criteria);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    #[IsGranted('ROLE_USER')]
    public function moderated(?string $sortBy, ?string $time, Request $request): Response
    {
        $params = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));
        $criteria->moderated = true;

        $params['comments'] = $this->repository->findByCriteria($criteria);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    #[IsGranted('ROLE_USER')]
    public function favourite(?string $sortBy, ?string $time, Request $request): Response
    {
        $params = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setFederation('false' === $request->cookies->get(ThemeSettingsController::KBIN_FEDERATION_ENABLED, true) ? Criteria::AP_LOCAL : Criteria::AP_ALL)
            ->setTime($criteria->resolveTime($time));
        $criteria->favourite = true;

        $params['comments'] = $this->repository->findByCriteria($criteria);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }
}

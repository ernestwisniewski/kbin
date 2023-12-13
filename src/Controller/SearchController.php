<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\Controller\User\ThemeSettingsController;
use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Entry\EntryPageView;
use App\Message\ActivityPub\Inbox\ActivityMessage;
use App\Repository\AggregateRepository;
use App\Repository\Criteria;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use App\Service\SearchManager;
use App\Service\SettingsManager;
use App\Utils\RegPatterns;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class SearchController extends AbstractController
{
    public function __construct(
        private readonly SearchManager $manager,
        private readonly AggregateRepository $aggregateRepository,
        private readonly ActivityPubManager $activityPubManager,
        private readonly MessageBusInterface $bus,
        private readonly ApHttpClient $apHttpClient,
        private readonly SettingsManager $settingsManager
    ) {
    }

    public function __invoke(?string $sortBy, ?string $time, ?string $type, Request $request): Response
    {
        $query = $request->query->get('q') ? trim($request->query->get('q')) : null;

        if (!$query) {
            return $this->render(
                'search/front.html.twig',
                [
                    'objects' => [],
                    'results' => [],
                    'q' => '',
                ]
            );
        }

        $objects = [];
        if (str_contains($query, '@') && (!$this->settingsManager->get(
            'KBIN_FEDERATED_SEARCH_ONLY_LOGGEDIN'
        ) || $this->getUser())) {
            $name = str_starts_with($query, '@') ? $query : '@'.$query;
            preg_match(RegPatterns::AP_USER, $name, $matches);
            if (\count(array_filter($matches)) >= 4) {
                try {
                    $webfinger = $this->activityPubManager->webfinger($name);
                    foreach ($webfinger->getProfileIds() as $profileId) {
                        $object = $this->activityPubManager->findActorOrCreate($profileId);

                        if ($object instanceof Magazine) { // @todo
                            $type = 'magazine';
                        } elseif ($object instanceof User) {
                            $type = 'user';
                        }

                        $objects[] = [
                            'type' => $type,
                            'object' => $object,
                        ];
                    }
                } catch (\Exception $e) {
                }
            }
        }

        if (false !== filter_var($query, FILTER_VALIDATE_URL)) {
            $objects = $this->manager->findByApId($query);
            if (!$objects) {
                $body = $this->apHttpClient->getActivityObject($query, false);
                $this->bus->dispatch(new ActivityMessage($body));
            }
        }

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
        $criteria->search = $request->query->get('q');

        $results = $this->aggregateRepository->findByCriteria($criteria, true);

        return $this->render(
            'search/front.html.twig',
            [
                'objects' => $objects,
                'results' => $results,
                'q' => $request->query->get('q'),
            ]
        );
    }
}

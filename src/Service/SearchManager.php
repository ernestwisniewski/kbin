<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Entity\Magazine;
use App\Entity\User;
use App\Message\ActivityPub\Inbox\ActivityMessage;
use App\Repository\DomainRepository;
use App\Repository\MagazineRepository;
use App\Repository\SearchRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Utils\RegPatterns;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SearchManager
{
    public function __construct(
        private readonly SearchRepository $repository,
        private readonly MagazineRepository $magazineRepository,
        private readonly DomainRepository $domainRepository,
        private readonly ActivityPubManager $activityPubManager,
        private readonly MessageBusInterface $bus,
        private readonly ApHttpClient $apHttpClient,
    ) {
    }

    /**
     * Not implemented yet.
     */
    public function findByTagPaginated(string $val, int $page = 1): PagerfantaInterface
    {
        return new Pagerfanta(new ArrayAdapter([]));
    }

    public function findMagazinesPaginated(string $magazine, int $page = 1, int $perPage = MagazineRepository::PER_PAGE): PagerfantaInterface
    {
        return $this->magazineRepository->search($magazine, $page, $perPage);
    }

    public function findDomainsPaginated(string $domain, int $page = 1, int $perPage = DomainRepository::PER_PAGE): PagerfantaInterface
    {
        return $this->domainRepository->search($domain, $page, $perPage);
    }

    public function findPaginated(string $val, int $page = 1, int $perPage = SearchRepository::PER_PAGE): PagerfantaInterface
    {
        return $this->repository->search($val, $page, $perPage);
    }

    public function findByApId(string $url): array
    {
        return $this->repository->findByApId($url);
    }

    public function findRelated(string $query): array
    {
        return [];
    }

    /**
     * @param string $query One or more canonical ActivityPub usernames, such as kbinMeta@kbin.social or @ernest@kbin.social (anything that matches RegPatterns::AP_USER)
     *
     * @return array a list of magazines or users that were found using the given identifiers, empty if none were found or no @ is in the query
     */
    public function findActivityPubActorsByUsername(string $query): array
    {
        if (false === str_contains($query, '@')) {
            return [];
        }

        $objects = [];
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

        return $objects ?? [];
    }

    /**
     * @param string $query a string that may or may not be a URL
     *
     * @return array A list of objects found by the given query, or an empty array if none were found.
     *               Will dispatch a getActivityObject request if a valid URL was provided but no item was found
     *               locally.
     */
    public function findActivityPubObjectsByURL(string $query): array
    {
        if (false === filter_var($query, FILTER_VALIDATE_URL)) {
            return [];
        }

        $objects = $this->findByApId($query);
        if (!$objects) {
            $body = $this->apHttpClient->getActivityObject($query, false);
            $this->bus->dispatch(new ActivityMessage($body));
        }

        return $objects ?? [];
    }
}

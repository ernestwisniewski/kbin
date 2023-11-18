<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Repository\StatsContentRepository;
use App\Service\SettingsManager;

readonly class NodeInfoFactory
{
    public const KBIN_CANONICAL_NAME = 'kbin';
    public const KBIN_VERSION = '0.10.1';
    public const KBIN_HOMEPAGE = 'https://kbin.pub';
    public const KBIN_REPOSITORY = 'https://codeberg.org/Kbin/kbin-core';
    public const NODE_REL_V2_0 = 'https://nodeinfo.diaspora.software/ns/schema/2.0';
    public const NODE_REL_V2_1 = 'https://nodeinfo.diaspora.software/ns/schema/2.1';
    public const NODE_PROTOCOL = 'activitypub';

    public function __construct(
        private StatsContentRepository $repository,
        private SettingsManager $settingsManager,
    ) {
    }

    public function create(string $version): array
    {
        $software = match ($version) {
            '2.0' => [
                'name' => self::KBIN_CANONICAL_NAME,
                'version' => self::KBIN_VERSION,
            ],
            '2.1' => [
                'name' => self::KBIN_CANONICAL_NAME,
                'version' => self::KBIN_VERSION,
                'repository' => self::KBIN_REPOSITORY,
                'homepage' => self::KBIN_HOMEPAGE,
            ],
        };

        return [
            'version' => $version,
            'software' => $software,
            'protocols' => [
                self::NODE_PROTOCOL,
            ],
            'services' => [
                'outbound' => [],
                'inbound' => [],
            ],
            'openRegistrations' => $this->settingsManager->get('KBIN_REGISTRATIONS_ENABLED'),
            'usage' => [
                'users' => [
                    'total' => $this->repository->countUsers(),
                    'activeHalfyear' => $this->repository->countUsers(new \DateTime('-6 months')),
                    'activeMonth' => $this->repository->countUsers(new \DateTime('-1 month')),
                ],
                'localPosts' => $this->repository->countLocalPosts(),
                'localComments' => $this->repository->countLocalComments(),
            ],
            'metadata' => (object) [],
        ];
    }
}

<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Repository\StatsContentRepository;
use App\Service\SettingsManager;

class NodeInfoFactory
{
    const NODE_REL = 'http://nodeinfo.diaspora.software/ns/schema/2.0';
    const NODE_PROTOCOL = 'activitypub';

    public function __construct(private StatsContentRepository $repository, private SettingsManager $settingsManager)
    {
    }

    public function create(): array
    {
        return [
            'version' => '2.0',
            'software' => [
                'name' => 'kbin',
                'version' => '0.10.1',
            ],
            'protocols' => [
                self::NODE_PROTOCOL,
            ],
            'services' => [
                'outbound' => [],
                'inbound' => [],
            ],
            'usage' => [
                'users' => [
                    'total' => $this->repository->countUsers(),
                    'activeHalfyear' => $this->repository->countUsers((new \DateTime('now'))->modify('-6 months')),
                    'activeMonth' => $this->repository->countUsers((new \DateTime('now'))->modify('-1 month')),
                ],
                'localPosts' => $this->repository->countLocalPosts(),
                'localComments' => $this->repository->countLocalComments(),
            ],
            'openRegistrations' => $this->settingsManager->get('KBIN_REGISTRATIONS_ENABLED'),
            'metadata' => [],
        ];
    }
}

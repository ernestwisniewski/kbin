<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Magazine;
use App\Service\ImageManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ImageManager $imageManager
    ) {
    }

    public function create(Magazine $magazine): array
    {
        $group = [
            'type' => 'Group',
            '@context' => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id' => $this->getActivityPubId($magazine),
            'name' => $magazine->name, // lemmy
            'preferredUsername' => $magazine->title,
            'inbox' => $this->urlGenerator->generate(
                'ap_magazine_inbox',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'outbox' => $this->urlGenerator->generate(
                'ap_magazine_outbox',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'followers' => $this->urlGenerator->generate(
                'ap_magazine_followers',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'url' => $this->getActivityPubId($magazine),
            'publicKey' => [
                'owner' => $this->getActivityPubId($magazine),
                'id' => $this->getActivityPubId($magazine).'#main-key',
                'publicKeyPem' => $magazine->publicKey,
            ],
            'summary' => $magazine->description,
            'sensitive' => $magazine->isAdult,
            'moderators' => $this->urlGenerator->generate(
                'ap_magazine_moderators',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'postingRestrictedToMods' => false,
            'endpoints' => [
                'sharedInbox' => $this->urlGenerator->generate(
                    'ap_shared_inbox',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            'published' => $magazine->createdAt->format(DATE_ATOM),
            'updated' => $magazine->lastActive->format(DATE_ATOM),
        ];

        if ($magazine->cover) {
            $group['icon'] = [
                'type' => 'Image',
                'url' => $this->imageManager->getUrl($magazine->cover),
            ];
        }

        return $group;
    }

    public function getActivityPubId(Magazine $magazine): string
    {
        if ($magazine->apId) {
            return $magazine->apId;
        }

        return $this->urlGenerator->generate(
            'ap_magazine',
            ['name' => $magazine->name],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}

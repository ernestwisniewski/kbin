<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Magazine;
use App\Service\SettingsManager;
use DateTimeInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SettingsManager $settings
    ) {
    }

    public function create(Magazine $magazine): array
    {
        $group = [
            'type'              => 'Group',
            '@context'          => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id'                => $this->getActivityPubId($magazine),
            'name'              => $magazine->title, // lemmy
            'preferredUsername' => $magazine->name,
            'inbox'             => $this->urlGenerator->generate(
                'ap_magazine_inbox',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'outbox'            => $this->urlGenerator->generate(
                'ap_magazine_outbox',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'followers'         => $this->urlGenerator->generate(
                'ap_magazine_followers',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'url'               => $this->getActivityPubId($magazine),
            'publicKey'         => [
                'owner'        => $this->getActivityPubId($magazine),
                'id'           => $this->getActivityPubId($magazine).'#main-key',
                'publicKeyPem' => $magazine->publicKey,
            ],
            'summary'           => $magazine->description,
            'sensitive'         => $magazine->isAdult,
            'moderators'        => $this->urlGenerator->generate(
                'ap_magazine_moderators',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'published'         => $magazine->createdAt->format(DATE_ATOM),
            'updated'           => $magazine->lastActive->format(DATE_ATOM),
        ];

        if ($magazine->cover) {
            $group['icon'] = [
                'type' => 'Image',
                'url'  => 'https://'.$this->settings->get('KBIN_DOMAIN').'/media/'.$magazine->cover->filePath  // @todo media url
            ];
        }

        return $group;
    }

    public function getActivityPubId(Magazine $magazine): string
    {
        if ($magazine->apId) {
            return $magazine->apId;
        }

        return $this->urlGenerator->generate('ap_magazine', ['name' => $magazine->name], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

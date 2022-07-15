<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\Magazine;
use Symfony\Component\HttpFoundation\RequestStack;

class GroupFactory
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private RequestStack $requestStack)
    {
    }

    public function create(Magazine $magazine): array
    {
        return [
            'type'              => 'Group',
            '@context'          => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id'                => $this->getActivityPubId($magazine),
            'name'              => $magazine->title, // lemmy
            'preferredUsername' => $magazine->name,
            'inbox'             => $this->urlGenerator->generate('ap_magazine_inbox', ['name' => $magazine->name], UrlGeneratorInterface::ABS_URL),
            'outbox'            => $this->urlGenerator->generate('ap_magazine_outbox', ['name' => $magazine->name], UrlGeneratorInterface::ABS_URL),
            'followers'         => $this->urlGenerator->generate(
                'ap_magazine_followers',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABS_URL
            ),
            'url'               => $this->getActivityPubId($magazine),
            'publicKey'         => [
                'owner'        => $this->getActivityPubId($magazine),
                'id'           => $this->getActivityPubId($magazine).'#main-key',
                'publicKeyPem' => '', // @todo public key
            ],
            'summary'           => $magazine->description,
        ];
    }


    public function getActivityPubId(Magazine $magazine): string
    {
        return $this->urlGenerator->generate('ap_magazine', ['name' => $magazine->name], UrlGeneratorInterface::ABS_URL);
    }
}

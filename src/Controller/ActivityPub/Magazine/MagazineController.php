<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\Magazine;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\Magazine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MagazineController
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(Magazine $magazine, Request $request): JsonResponse
    {
        $group = [
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

        $response = new JsonResponse($group);

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }

    private function getActivityPubId(Magazine $magazine): string
    {
        return $this->urlGenerator->generate('ap_magazine', ['name' => $magazine->name], UrlGeneratorInterface::ABS_URL);
    }
}

<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

class PersonFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack
    ) {
    }

    public function create(User $user): array
    {
        $person = [
            'type'              => 'Person',
            '@context'          => [ActivityPubActivityInterface::CONTEXT_URL, ActivityPubActivityInterface::SECURITY_URL],
            'id'                => $this->getActivityPubId($user),
            'name'              => $user->username,
            'preferredUsername' => $user->username,
            'inbox'             => $this->urlGenerator->generate('ap_user_inbox', ['username' => $user->username], UrlGeneratorInterface::ABS_URL),
            'outbox'            => $this->urlGenerator->generate('ap_user_outbox', ['username' => $user->username], UrlGeneratorInterface::ABS_URL),
            'following'         => $this->urlGenerator->generate(
                'ap_user_following',
                ['username' => $user->username],
                UrlGeneratorInterface::ABS_URL
            ),
            'followers'         => $this->urlGenerator->generate(
                'ap_user_followers',
                ['username' => $user->username],
                UrlGeneratorInterface::ABS_URL
            ),
            'url'               => $this->getActivityPubId($user),
            'publicKey'         => [
                'owner'        => $this->getActivityPubId($user),
                'id'           => $this->getActivityPubId($user).'#main-key',
                'publicKeyPem' => $user->publicKey,
            ],
        ];

        if ($user->avatar) {
            $person['icon'] = [
                'type' => 'Image',
                'url'  => $this->requestStack->getCurrentRequest()->getUriForPath('/media/'.$user->avatar->filePath) // @todo media url
            ];
        }

        return $person;
    }


    public function getActivityPubId(User $user): string
    {
        return $this->urlGenerator->generate('ap_user', ['username' => $user->username], UrlGeneratorInterface::ABS_URL);
    }
}

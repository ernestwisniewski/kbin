<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\User;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PersonFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack
    ) {
    }

    public function create(User $user, bool $context = true): array
    {
        if ($context) {
            $person ['@context'] = [
                ActivityPubActivityInterface::CONTEXT_URL,
                ActivityPubActivityInterface::SECURITY_URL,
                $this->getContext(),
            ];
        }

        $person = array_merge(
            $person ?? [], [
                'id'                        => $this->getActivityPubId($user),
                'type'                      => 'Person',
                'name'                      => $user->username,
                'preferredUsername'         => $user->username,
                'inbox'                     => $this->urlGenerator->generate(
                    'ap_user_inbox',
                    ['username' => $user->username],
                    UrlGeneratorInterface::ABS_URL
                ),
                'outbox'                    => $this->urlGenerator->generate(
                    'ap_user_outbox',
                    ['username' => $user->username],
                    UrlGeneratorInterface::ABS_URL
                ),
                'url'                       => $this->getActivityPubId($user),
                'manuallyApprovesFollowers' => false,
                'published'                 => $user->createdAt->format(DateTimeInterface::ISO8601),
                'following'                 => $this->urlGenerator->generate(
                    'ap_user_following',
                    ['username' => $user->username],
                    UrlGeneratorInterface::ABS_URL
                ),
                'followers'                 => $this->urlGenerator->generate(
                    'ap_user_followers',
                    ['username' => $user->username],
                    UrlGeneratorInterface::ABS_URL
                ),
                'publicKey'                 => [
                    'owner'        => $this->getActivityPubId($user),
                    'id'           => $this->getActivityPubId($user).'#main-key',
                    'publicKeyPem' => $user->publicKey,
                ],
                'endpoints'                 => [
                    'sharedInbox' => $this->urlGenerator->generate('ap_shared_inbox', [], UrlGeneratorInterface::ABS_URL),
                ],
            ]
        );

        if ($user->avatar) {
            $person['icon'] = [
                'type' => 'Image',
                'url'  => $this->requestStack->getCurrentRequest()->getUriForPath('/media/'.$user->avatar->filePath) // @todo media url
            ];
        }

        return $person;
    }

    public function getContext(): array
    {
        return [
            'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
            'schema'                    => 'http://schema.org#',
            'PropertyValue'             => 'schema:PropertyValue',
            'value'                     => 'schema:value',
        ];
    }

    public function getActivityPubId(User $user): string
    {
        return $this->urlGenerator->generate('ap_user', ['username' => $user->username], UrlGeneratorInterface::ABS_URL);
    }
}

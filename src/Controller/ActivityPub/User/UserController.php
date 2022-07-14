<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\User;
use App\Service\ActivityPubManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController
{
    public function __construct(private ActivityPubManager $activityPubUtility, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(User $user, Request $request): JsonResponse
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
                'publicKeyPem' => '',
            ],
        ];

        if ($user->avatar) {
            $person['icon'] = [
                'type' => 'Image',
                'url'  => $request->getUriForPath('/media/'.$user->avatar->filePath) // @todo media url
            ];
        }

        $response = new JsonResponse($person);

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }

    private function getActivityPubId(User $user): string
    {
        return $this->urlGenerator->generate('ap_user', ['username' => $user->username], UrlGeneratorInterface::ABS_URL);
    }
}

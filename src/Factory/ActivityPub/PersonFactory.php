<?php

declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\User;
use App\Markdown\MarkdownConverter;
use App\Markdown\RenderTarget;
use App\Service\ImageManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonFactory
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ImageManager $imageManager,
        private readonly MarkdownConverter $markdownConverter
    ) {
    }

    public function create(User $user, bool $context = true): array
    {
        if ($context) {
            $person['@context'] = [
                ActivityPubActivityInterface::CONTEXT_URL,
                ActivityPubActivityInterface::SECURITY_URL,
                $this->getContext(),
            ];
        }

        $person = array_merge(
            $person ?? [], [
                'id' => $this->getActivityPubId($user),
                'type' => 'Person',
                'name' => $user->username,
                'preferredUsername' => $user->username,
                'inbox' => $this->urlGenerator->generate(
                    'ap_user_inbox',
                    ['username' => $user->username],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'outbox' => $this->urlGenerator->generate(
                    'ap_user_outbox',
                    ['username' => $user->username],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'url' => $this->getActivityPubId($user),
                'manuallyApprovesFollowers' => false,
                'published' => $user->createdAt->format(DATE_ATOM),
                'following' => $this->urlGenerator->generate(
                    'ap_user_following',
                    ['username' => $user->username],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'followers' => $this->urlGenerator->generate(
                    'ap_user_followers',
                    ['username' => $user->username],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'publicKey' => [
                    'owner' => $this->getActivityPubId($user),
                    'id' => $this->getActivityPubId($user).'#main-key',
                    'publicKeyPem' => $user->publicKey,
                ],
                'endpoints' => [
                    'sharedInbox' => $this->urlGenerator->generate(
                        'ap_shared_inbox',
                        [],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ],
            ]
        );

        if ($user->about) {
            $person['summary'] = $this->markdownConverter->convertToHtml(
                $user->about,
                [MarkdownConverter::RENDER_TARGET => RenderTarget::ActivityPub],
            );
        }

        if ($user->cover) {
            $person['image'] = [
                'type' => 'Image',
                'url' => $this->imageManager->getUrl($user->cover),
                // @todo media url
            ];
        }

        if ($user->avatar) {
            $person['icon'] = [
                'type' => 'Image',
                'url' => $this->imageManager->getUrl($user->avatar),
                // @todo media url
            ];
        }

        return $person;
    }

    #[ArrayShape([
        'manuallyApprovesFollowers' => 'string',
        'schema' => 'string',
        'PropertyValue' => 'string',
        'value' => 'string',
    ])]
    public function getContext(): array
    {
        return [
            'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
            'schema' => 'http://schema.org#',
            'PropertyValue' => 'schema:PropertyValue',
            'value' => 'schema:value',
        ];
    }

    public function getActivityPubId(User $user): string
    {
        if ($user->apId) {
            return $user->apProfileId;
        }

        return $this->urlGenerator->generate(
            'ap_user',
            ['username' => $user->username],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}

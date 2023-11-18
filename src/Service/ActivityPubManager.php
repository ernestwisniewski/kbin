<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub\Server;
use App\DTO\ActivityPub\ImageDto;
use App\DTO\ActivityPub\VideoDto;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Contracts\ActivityPubActorInterface;
use App\Entity\Contracts\ContentInterface;
use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use App\Factory\ActivityPub\PersonFactory;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Kbin\Magazine\MagazineCreate;
use App\Kbin\User\Factory\UserFactory;
use App\Kbin\User\UserCreate;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\UpdateActorMessage;
use App\Message\DeleteImageMessage;
use App\Repository\ImageRepository;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Webfinger\WebFinger;
use App\Service\ActivityPub\Webfinger\WebFingerFactory;
use Doctrine\ORM\EntityManagerInterface;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityPubManager
{
    public function __construct(
        private Server $server,
        private UserRepository $userRepository,
        private UserCreate $userCreate,
        private UserFactory $userFactory,
        private MagazineCreate $magazineCreate,
        private MagazineFactory $magazineFactory,
        private MagazineRepository $magazineRepository,
        private ApHttpClient $apHttpClient,
        private ImageRepository $imageRepository,
        private ImageManager $imageManager,
        private EntityManagerInterface $entityManager,
        private PersonFactory $personFactory,
        private SettingsManager $settingsManager,
        private WebFingerFactory $webFingerFactory,
        private MentionManager $mentionManager,
        private UrlGeneratorInterface $urlGenerator,
        private MessageBusInterface $bus
    ) {
    }

    public function getActorProfileId(ActivityPubActorInterface $actor): string
    {
        /**
         * @var $actor User
         */
        if (!$actor->apId) {
            return $this->personFactory->getActivityPubId($actor);
        }

        // @todo blid webfinger
        return $actor->apProfileId;
    }

    public function findRemoteActor(string $actorUrl): ?User
    {
        return $this->userRepository->findOneBy(['apProfileId' => $actorUrl]);
    }

    public function createCcFromBody(string $body): array
    {
        $mentions = $this->mentionManager->extract($body) ?? [];

        $urls = [];
        foreach ($mentions as $handle) {
            try {
                $actor = $this->findActorOrCreate($handle);
            } catch (\Exception $e) {
                continue;
            }

            if (!$actor) {
                continue;
            }

            $urls[] = $actor->apProfileId ?? $this->urlGenerator->generate(
                'ap_user',
                ['username' => $actor->getUserIdentifier()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $urls;
    }

    public function findActorOrCreate(string $actorUrlOrHandle): null|User|Magazine
    {
        if (str_contains($actorUrlOrHandle, $this->settingsManager->get('KBIN_DOMAIN').'/m/')) {
            $magazine = str_replace('https://'.$this->settingsManager->get('KBIN_DOMAIN').'/m/', '', $actorUrlOrHandle);

            return $this->magazineRepository->findOneByName($magazine);
        }

        $actorUrl = $actorUrlOrHandle;
        if (false === filter_var($actorUrl, FILTER_VALIDATE_URL)) {
            if (!substr_count(ltrim($actorUrl, '@'), '@')) {
                return $this->userRepository->findOneBy(['username' => ltrim($actorUrl, '@')]);
            }

            $actorUrl = $this->webfinger($actorUrl)->getProfileId();
        }

        if (\in_array(
            parse_url($actorUrl, PHP_URL_HOST),
            [$this->settingsManager->get('KBIN_DOMAIN'), 'localhost', '127.0.0.1']
        )) {
            $name = explode('/', $actorUrl);
            $name = end($name);

            return $this->userRepository->findOneBy(['username' => $name]);
        }

        $actor = $this->apHttpClient->getActorObject($actorUrl);

        if ('Person' === $actor['type']) {
            // User
            $user = $this->userRepository->findOneBy(['apProfileId' => $actorUrl]);
            if (!$user) {
                $user = $this->createUser($actorUrl);
            } else {
                if (!$user->apFetchedAt || $user->apFetchedAt->modify('+1 hour') < (new \DateTime())) {
                    try {
                        $this->bus->dispatch(new UpdateActorMessage($user->apProfileId));
                    } catch (\Exception $e) {
                    }
                }
            }

            return $user;
        }

        // Magazine
        if ('Group' === $actor['type']) {
            // User
            $magazine = $this->magazineRepository->findOneBy(['apProfileId' => $actorUrl]);
            if (!$magazine) {
                $magazine = $this->createMagazine($actorUrl);
            } else {
                if (!$magazine->apFetchedAt || $magazine->apFetchedAt->modify('+1 hour') < (new \DateTime())) {
                    try {
                        $this->bus->dispatch(new UpdateActorMessage($magazine->apProfileId));
                    } catch (\Exception $e) {
                    }
                }
            }

            return $magazine;
        }

        return null;
    }

    public function webfinger(string $id): WebFinger
    {
        $this->webFingerFactory::setServer($this->server->create());

        if (false === filter_var($id, FILTER_VALIDATE_URL)) {
            $id = ltrim($id, '@');

            return $this->webFingerFactory->get($id);
        }

        $handle = $this->buildHandle($id);

        return $this->webFingerFactory->get($handle);
    }

    public function buildHandle(string $id): string
    {
        $port = !\is_null(parse_url($id, PHP_URL_PORT))
            ? ':'.parse_url($id, PHP_URL_PORT)
            : '';

        return sprintf(
            '%s@%s%s',
            $this->apHttpClient->getActorObject($id)['preferredUsername'],
            parse_url($id, PHP_URL_HOST),
            $port
        );
    }

    private function createUser(string $actorUrl): User
    {
        $webfinger = $this->webfinger($actorUrl);
        ($this->userCreate)(
            $this->userFactory->createDtoFromAp($actorUrl, $webfinger->getHandle()),
            false,
            false
        );

        return $this->updateUser($actorUrl);
    }

    public function updateUser(string $actorUrl): User
    {
        $user = $this->userRepository->findOneBy(['apProfileId' => $actorUrl]);

        $actor = $this->apHttpClient->getActorObject($actorUrl);

        if (isset($actor['summary'])) {
            $converter = new HtmlConverter(['strip_tags' => true]);
            $user->about = stripslashes($converter->convert($actor['summary']));
        }

        if (isset($actor['icon'])) {
            $newImage = $this->handleImages([$actor['icon']]);
            if ($user->avatar && $newImage !== $user->avatar) {
                $this->bus->dispatch(new DeleteImageMessage($user->avatar->filePath));
            }
            $user->avatar = $newImage;
        }

        if (isset($actor['image'])) {
            $newImage = $this->handleImages([$actor['image']]);
            if ($user->cover && $newImage !== $user->cover) {
                $this->bus->dispatch(new DeleteImageMessage($user->cover->filePath));
            }
            $user->cover = $newImage;
        }

        $user->apInboxUrl = $actor['endpoints']['sharedInbox'] ?? $actor['inbox'];
        $user->apDomain = parse_url($actor['id'], PHP_URL_HOST);
        $user->apFollowersUrl = $actor['followers'] ?? null;
        $user->apPreferredUsername = $actor['preferredUsername'] ?? null;
        $user->apDiscoverable = $actor['discoverable'] ?? true;
        $user->apManuallyApprovesFollowers = $actor['manuallyApprovesFollowers'] ?? false;
        $user->apPublicUrl = $actor['url'] ?? $actorUrl;
        $user->apDeletedAt = null;
        $user->apTimeoutAt = null;
        $user->apFetchedAt = new \DateTime();

        $this->entityManager->flush();

        return $user;
    }

    public function handleImages(array $attachment): ?Image
    {
        $images = array_filter(
            $attachment,
            fn ($val) => \in_array($val['type'], ['Document', 'Image']) && ImageManager::isImageUrl($val['url'])
        ); // @todo multiple images

        if (\count($images)) {
            try {
                if ($tempFile = $this->imageManager->download($images[0]['url'])) {
                    $image = $this->imageRepository->findOrCreateFromPath($tempFile);
                    if ($image && isset($images[0]['name'])) {
                        $image->altText = $images[0]['name'];
                    }
                }
            } catch (\Exception $e) {
                return null;
            }

            return $image ?? null;
        }

        return null;
    }

    private function createMagazine(string $actorUrl): Magazine
    {
        ($this->magazineCreate)(
            $this->magazineFactory->createDtoFromAp($actorUrl, $this->buildHandle($actorUrl)),
            $this->userRepository->findAdmin(),
            false
        );

        return $this->updateMagazine($actorUrl);
    }

    public function updateMagazine(string $actorUrl): Magazine
    {
        $magazine = $this->magazineRepository->findOneBy(['apProfileId' => $actorUrl]);
        $actor = $this->apHttpClient->getActorObject($actorUrl);

        if (isset($actor['summary'])) {
            $converter = new HtmlConverter(['strip_tags' => true]);
            $magazine->description = stripslashes($converter->convert($actor['summary']));
        }

        if (isset($actor['icon'])) {
            $newImage = $this->handleImages([$actor['icon']]);
            if ($magazine->icon && $newImage !== $magazine->icon) {
                $this->bus->dispatch(new DeleteImageMessage($magazine->icon->filePath));
            }
            $magazine->icon = $newImage;
        }

        if ($actor['preferredUsername']) {
            $magazine->title = $actor['preferredUsername'];
        }

        $magazine->apInboxUrl = $actor['endpoints']['sharedInbox'] ?? $actor['inbox'];
        $magazine->apDomain = parse_url($actor['id'], PHP_URL_HOST);
        $magazine->apFollowersUrl = $actor['followers'] ?? null;
        $magazine->apPreferredUsername = $actor['preferredUsername'] ?? null;
        $magazine->apDiscoverable = $actor['discoverable'] ?? true;
        $magazine->apPublicUrl = $actor['url'] ?? $actorUrl;
        $magazine->apDeletedAt = null;
        $magazine->apTimeoutAt = null;
        $magazine->apFetchedAt = new \DateTime();

        $this->entityManager->flush();

        return $magazine;
    }

    public function createInboxesFromCC(array $activity, User $user): array
    {
        $followersUrl = $this->urlGenerator->generate(
            'ap_user_followers',
            ['username' => $user->username],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $arr = array_unique(
            array_filter(
                array_merge(
                    \is_array($activity['cc']) ? $activity['cc'] : [$activity['cc']],
                    \is_array($activity['to']) ? $activity['to'] : [$activity['to']]
                ), fn ($val) => !\in_array($val, [ActivityPubActivityInterface::PUBLIC_URL, $followersUrl, []])
            )
        );

        $users = [];
        foreach ($arr as $url) {
            if ($user = $this->findActorOrCreate($url)) {
                $users[] = $user;
            }
        }

        return array_map(fn ($user) => $user->apInboxUrl, $users);
    }

    public function handleVideos(array $attachment): ?VideoDto
    {
        $videos = array_filter(
            $attachment,
            fn ($val) => \in_array($val['type'], ['Document', 'Video']) && VideoManager::isVideoUrl($val['url'])
        );

        if (\count($videos)) {
            return (new VideoDto())->create(
                $videos[0]['url'],
                $videos[0]['mediaType'],
                !empty($videos['0']['name']) ? $videos['0']['name'] : $videos['0']['mediaType']
            );
        }

        return null;
    }

    public function handleExternalImages(array $attachment): ?array
    {
        $images = array_filter(
            $attachment,
            fn ($val) => \in_array($val['type'], ['Document', 'Image']) && ImageManager::isImageUrl($val['url'])
        );

        array_shift($images);

        if (\count($images)) {
            return array_map(fn ($val) => (new ImageDto())->create(
                $val['url'],
                $val['mediaType'],
                !empty($val['name']) ? $val['name'] : $val['mediaType']
            ), $images);
        }

        return null;
    }

    public function handleExternalVideos(array $attachment): ?array
    {
        $videos = array_filter(
            $attachment,
            fn ($val) => \in_array($val['type'], ['Document', 'Video']) && VideoManager::isVideoUrl($val['url'])
        );

        if (\count($videos)) {
            return array_map(fn ($val) => (new VideoDto())->create(
                $val['url'],
                $val['mediaType'],
                !empty($val['name']) ? $val['name'] : $val['mediaType']
            ), $videos);
        }

        return null;
    }

    public function updateActor(string $actorUrl): Magazine|User
    {
        $actor = $this->apHttpClient->getActorObject($actorUrl);

        if ('Person' === $actor['type']) {
            return $this->updateUser($actorUrl);
        }

        return $this->updateMagazine($actorUrl);
    }

    public function resend(ContentInterface $entity): void
    {
        if (!$entity->apId) {
            $this->bus->dispatch(new CreateMessage($entity->getId(), \get_class($entity)));
        }
    }
}

<?php declare(strict_types=1);

namespace App\Service;

use App\ActivityPub\Server;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Contracts\ActivityPubActorInterface;
use App\Entity\Image;
use App\Entity\User;
use App\Factory\ActivityPub\PersonFactory;
use App\Factory\UserFactory;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\Webfinger\WebFinger;
use App\Service\ActivityPub\Webfinger\WebFingerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityPubManager
{
    public function __construct(
        private Server $server,
        private UserRepository $userRepository,
        private UserManager $userManager,
        private UserFactory $userFactory,
        private ApHttpClient $apHttpClient,
        private ImageRepository $imageRepository,
        private ImageManager $imageManager,
        private EntityManagerInterface $entityManager,
        private PersonFactory $personFactory,
        private SettingsManager $settingsManager,
        private WebFingerFactory $webFingerFactory,
        private MentionManager $mentionManager,
        private UrlGeneratorInterface $urlGenerator
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

    public function findActorOrCreate(string $actorUrlOrHandle): User
    {
        $actorUrl = $actorUrlOrHandle;
        if (false === filter_var($actorUrl, FILTER_VALIDATE_URL)) {
            if (!substr_count(ltrim($actorUrl, '@'), '@')) {
                return $this->userRepository->findOneBy(['username' => ltrim($actorUrl, '@')]);
            }

            $actorUrl = $this->webfinger($actorUrl)->getProfileId();
        }

        if (in_array(parse_url($actorUrl, PHP_URL_HOST), [$this->settingsManager->get('KBIN_DOMAIN'), 'localhost', '127.0.0.1'])) {
            $name = explode('/', $actorUrl);
            $name = end($name);

            return $this->userRepository->findOneBy(['username' => $name]);
        }

        $user = $this->userRepository->findOneBy(['apProfileId' => $actorUrl]);

        if (!$user) {
            $webfinger = $this->webfinger($actorUrl);
            $user      = $this->userManager->create($this->userFactory->createDtoFromAp($actorUrl, $webfinger->getHandle()), false, false);
            $actor     = $this->apHttpClient->getActivityObject($actorUrl, true);

            if (isset($actor['icon'])) {
                $user->avatar = $this->handleImages([$actor['icon']]);
            }

            $user->notifyOnNewEntry             = false;
            $user->notifyOnNewEntryReply        = false;
            $user->notifyOnNewEntryCommentReply = false;
            $user->notifyOnNewPost              = false;
            $user->notifyOnNewPostCommentReply  = false;

            $this->entityManager->flush();
        }

        return $user;
    }

    public function webfinger(string $id): WebFinger
    {
        $this->webFingerFactory::setServer($this->server->create());

        if (filter_var($id, FILTER_VALIDATE_URL) === false) {
            $id = ltrim($id, '@');

            return $this->webFingerFactory->get($id);
        }

        $port = !is_null(parse_url($id, PHP_URL_PORT))
            ? ':'.parse_url($id, PHP_URL_PORT)
            : '';

        $handle = sprintf(
            '%s@%s%s',
            $this->apHttpClient->getActivityObject($id)['preferredUsername'],
            parse_url($id, PHP_URL_HOST),
            $port
        );

        return $this->webFingerFactory->get($handle);
    }

    public function handleImages(array $attachment): ?Image
    {
        $images = array_filter(
            $attachment,
            fn($val) => in_array($val['type'], ['Document', 'Image']) && ImageManager::isImageUrl($val['url'])
        ); // @todo multiple images

        if (count($images)) {
            try {
                if ($tempFile = $this->imageManager->download($images[0]['url'])) {
                    $image = $this->imageRepository->findOrCreateFromPath($tempFile);
                }
            } catch (\Exception $e) {
            }

            return $image ?? null;
        }

        return null;
    }

    public function createCcFromBody(string $body): array
    {
        $mentions = $this->mentionManager->extract($body) ?? [];

        $urls = [];
        foreach ($mentions as $handle) {
            $actor  = $this->findActorOrCreate($handle);
            $urls[] = $actor->apProfileId ?? $this->urlGenerator->generate(
                    'ap_user',
                    ['username' => $actor->getUserIdentifier()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
        }

        return $urls;
    }

    public function getFollowersFromObject(array $activity, User $user): array
    {
        if (isset($activity['cc']) && isset($activity['to'])) {
            $followersUrl = $this->urlGenerator->generate(
                'ap_user_followers',
                ['username' => $user->username],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return array_unique(
                array_filter(
                    array_merge(
                        is_array($activity['cc']) ? $activity['cc'] : [$activity['cc']],
                        is_array($activity['to']) ? $activity['to'] : [$activity['to']]
                    ), fn($val) => !in_array($val, [ActivityPubActivityInterface::PUBLIC_URL, $followersUrl])
                )
            );
        }

        return [];
    }
}

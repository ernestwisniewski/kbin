<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ActivityPubManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserFollowController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private ActivityPubManager $activityPubManager
    ) {
    }

    public function followers(User $user, Request $request): JsonResponse
    {
        return $this->get($user, $request, ActivityPubActivityInterface::FOLLOWERS);
    }

    public function following(User $user, Request $request): JsonResponse
    {
        return $this->get($user, $request, ActivityPubActivityInterface::FOLLOWING);
    }

    public function get(User $user, Request $request, string $type): JsonResponse
    {
        $page = $request->get('page', 0);

        if (!$page) {
            $data = $this->getCollectionInfo($user, $type);
        } else {
            $data = $this->getCollectionItems($user, (int) $page, $type);
        }

        $response = new JsonResponse($data);

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }

    #[ArrayShape([
        '@context'   => "string",
        'type'       => "string",
        'id'         => "string",
        'first'      => "string",
        'totalItems' => "int",
    ])] private function getCollectionInfo(User $user, string $type): array
    {
        $routeName = "ap_user_{$type}";

        if ($type === ActivityPubActivityInterface::FOLLOWING) {
            $count = $this->userRepository->findFollowing(1, $user)->getNbResults();
        } else {
            $count = $this->userRepository->findFollowers(1, $user)->getNbResults();
        }

        return [
            '@context'   => ActivityPubActivityInterface::CONTEXT_URL,
            'type'       => 'OrderedCollection',
            'id'         => $this->urlGenerator->generate($routeName, ['username' => $user->username], UrlGeneratorInterface::ABS_URL),
            'first'      => $this->urlGenerator->generate(
                $routeName,
                ['username' => $user->username, 'page' => 1],
                UrlGeneratorInterface::ABS_URL
            ),
            'totalItems' => $count,
        ];
    }

    #[ArrayShape([
        '@context'     => "string",
        'type'         => "string",
        'partOf'       => "string",
        'id'           => "string",
        'totalItems'   => "int",
        'orderedItems' => "array",
    ])] private function getCollectionItems(User $user, int $page, string $type): array
    {
        $routeName = "ap_user_{$type}";

        if ($type === ActivityPubActivityInterface::FOLLOWING) {
            $actors = $this->userRepository->findFollowing($page, $user);
        } else {
            $actors = $this->userRepository->findFollowers($page, $user);
        }

        $items = [];
        foreach ($actors as $actor) {
            $items[] = $this->activityPubManager->getActivityPubProfileId($actor);
        }

        $result = [
            '@context'     => ActivityPubActivityInterface::CONTEXT_URL,
            'type'         => 'OrderedCollectionPage',
            'partOf'       => $this->urlGenerator->generate($routeName, ['username' => $user->username], UrlGeneratorInterface::ABS_URL),
            'id'           => $this->urlGenerator->generate(
                $routeName,
                ['username' => $user->username, 'page' => $page],
                UrlGeneratorInterface::ABS_URL
            ),
            'totalItems'   => $actors->getNbResults(),
            'orderedItems' => $items,
        ];

        if ($actors->hasNextPage()) {
            $result['next'] = $this->urlGenerator->generate(
                $routeName,
                ['username' => $user->username, 'page' => $actors->getNextPage()],
                UrlGeneratorInterface::ABS_URL
            );
        }

        return $result;
    }
}

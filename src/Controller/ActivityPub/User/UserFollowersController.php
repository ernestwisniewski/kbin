<?php

declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\CollectionInfoWrapper;
use App\Service\ActivityPub\Wrapper\CollectionItemsWrapper;
use App\Service\ActivityPubManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserFollowersController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ActivityPubManager $manager,
        private readonly CollectionInfoWrapper $collectionInfoWrapper,
        private readonly CollectionItemsWrapper $collectionItemsWrapper
    ) {
    }

    public function followers(User $user, Request $request): JsonResponse
    {
        return $this->get($user, $request, ActivityPubActivityInterface::FOLLOWERS);
    }

    public function get(User $user, Request $request, string $type): JsonResponse
    {
        if (!$request->get('page')) {
            $data = $this->getCollectionInfo($user, $type);
        } else {
            $data = $this->getCollectionItems($user, (int) $request->get('page'), $type);
        }

        $response = new JsonResponse($data);

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }

    #[ArrayShape([
        '@context' => 'string',
        'type' => 'string',
        'id' => 'string',
        'first' => 'string',
        'totalItems' => 'int',
    ])]
    private function getCollectionInfo(User $user, string $type): array
    {
        $routeName = "ap_user_{$type}";

        if (ActivityPubActivityInterface::FOLLOWING === $type) {
            $count = $this->userRepository->findFollowing(1, $user)->getNbResults();
        } else {
            $count = $this->userRepository->findFollowers(1, $user)->getNbResults();
        }

        return $this->collectionInfoWrapper->build($routeName, ['username' => $user->username], $count);
    }

    #[ArrayShape([
     '@context' => 'string',
     'type' => 'string',
     'partOf' => 'string',
     'id' => 'string',
     'totalItems' => 'int',
     'orderedItems' => 'array',
 ])]
    private function getCollectionItems(User $user, int $page, string $type): array
    {
        $routeName = "ap_user_{$type}";

        if (ActivityPubActivityInterface::FOLLOWING === $type) {
            $actors = $this->userRepository->findFollowing($page, $user);
        } else {
            $actors = $this->userRepository->findFollowers($page, $user);
        }

        $items = [];
        foreach ($actors as $actor) {
            $items[] = $this->manager->getActorProfileId($actor);
        }

        return $this->collectionItemsWrapper->build(
            $routeName,
            ['username' => $user->username],
            $actors,
            $items,
            $page
        );
    }

    public function following(User $user, Request $request): JsonResponse
    {
        return $this->get($user, $request, ActivityPubActivityInterface::FOLLOWING);
    }
}

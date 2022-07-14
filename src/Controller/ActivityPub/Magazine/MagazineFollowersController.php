<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\Magazine;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\Magazine;
use App\Repository\MagazineSubscriptionRepository;
use App\Service\ActivityPubManager;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MagazineFollowersController
{
    public function __construct(
        private ActivityPubManager $activityPubManager,
        private UrlGeneratorInterface $urlGenerator,
        private MagazineSubscriptionRepository $magazineSubscriptionRepository
    ) {
    }

    public function __invoke(Magazine $magazine, Request $request): JsonResponse
    {
        $page = $request->get('page', 0);


        if (!$page) {
            $data = $this->getCollectionInfo($magazine);
        } else {
            $data = $this->getCollectionItems($magazine, (int) $page);
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
    ])] private function getCollectionInfo(Magazine $magazine): array
    {
        $count = $this->magazineSubscriptionRepository->findMagazineSubscribers(1, $magazine)->count();

        return [
            '@context'   => ActivityPubActivityInterface::CONTEXT_URL,
            'type'       => 'OrderedCollection',
            'id'         => $this->urlGenerator->generate('ap_magazine_followers', ['name' => $magazine->name], UrlGeneratorInterface::ABS_URL),
            'first'      => $this->urlGenerator->generate(
                'ap_magazine_followers',
                ['name' => $magazine->name, 'page' => 1],
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
        'next'         => "string",
    ])] private function getCollectionItems(Magazine $magazine, int $page): array
    {
        $subscriptions = $this->magazineSubscriptionRepository->findMagazineSubscribers(1, $magazine);
        $actors        = array_map(fn($sub) => $sub->user, iterator_to_array($subscriptions->getCurrentPageResults()));

        $items = [];
        foreach ($actors as $actor) {
            $items[] = $this->activityPubManager->getActivityPubProfileId($actor);
        }

        $result = [
            '@context'     => ActivityPubActivityInterface::CONTEXT_URL,
            'type'         => 'OrderedCollectionPage',
            'partOf'       => $this->urlGenerator->generate('ap_magazine_followers', ['name' => $magazine->name], UrlGeneratorInterface::ABS_URL),
            'id'           => $this->urlGenerator->generate(
                'ap_magazine_followers',
                ['name' => $magazine->name, 'page' => $page],
                UrlGeneratorInterface::ABS_URL
            ),
            'totalItems'   => $subscriptions->getNbResults(),
            'orderedItems' => $items,
        ];

        if ($subscriptions->hasNextPage()) {
            $result['next'] = $this->urlGenerator->generate(
                'ap_magazine_followers',
                ['name' => $magazine->name, 'page' => $subscriptions->getNextPage()],
                UrlGeneratorInterface::ABS_URL
            );
        }

        return $result;
    }
}

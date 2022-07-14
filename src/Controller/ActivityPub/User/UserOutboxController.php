<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\ActivityPubActivityInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserOutboxController
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private UserRepository $userRepository)
    {
    }

    public function __invoke(User $user, Request $request): JsonResponse
    {
        $page = $request->get('page', 0);

        if (!$page) {
            $data = $this->getCollectionInfo($user);
        } else {
            $data = $this->getCollectionItems($user, (int) $page);
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
    ])] private function getCollectionInfo(User $user): array
    {
        return [
            '@context'   => ActivityPubActivityInterface::CONTEXT_URL,
            'type'       => 'OrderedCollection',
            'id'         => $this->urlGenerator->generate('ap_user_outbox', ['username' => $user->username], UrlGeneratorInterface::ABS_URL),
            'first'      => $this->urlGenerator->generate(
                'ap_user_outbox',
                ['username' => $user->username, 'page' => 1],
                UrlGeneratorInterface::ABS_URL
            ),
            'totalItems' => $this->userRepository->countPublicActivity($user),
        ];
    }

    #[ArrayShape(['@context'     => "string",
                  'type'         => "string",
                  'partOf'       => "string",
                  'id'           => "string",
                  'totalItems'   => "int",
                  'orderedItems' => "array"
    ])] private function getCollectionItems(
        User $user,
        int $page
    ): array {
        $items = $this->userRepository->findPublicActivity($page, $user);

        return [
            '@context'     => ActivityPubActivityInterface::CONTEXT_URL,
            'type'         => 'OrderedCollectionPage',
            'partOf'       => $this->urlGenerator->generate('ap_user_outbox', ['username' => $user->username], UrlGeneratorInterface::ABS_URL),
            'id'           => $this->urlGenerator->generate(
                'ap_user_outbox',
                ['username' => $user->username, 'page' => $page],
                UrlGeneratorInterface::ABS_URL
            ),
            'totalItems'   => $items->getNbResults(),
            'orderedItems' => [],
        ];
    }
}

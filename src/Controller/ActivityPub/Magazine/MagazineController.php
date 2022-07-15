<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\Magazine;

use App\Entity\Magazine;
use App\Factory\ActivityPub\GroupFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class MagazineController
{
    public function __construct(private GroupFactory $groupFactory)
    {
    }

    public function __invoke(Magazine $magazine): JsonResponse
    {
        $response = new JsonResponse($this->groupFactory->create($magazine));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}

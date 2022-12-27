<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Factory\ActivityPub\GroupFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class MagazineController extends AbstractController
{
    public function __construct(private GroupFactory $groupFactory)
    {
    }

    public function __invoke(Magazine $magazine): JsonResponse
    {
        if ($magazine->apId) {
            throw $this->createNotFoundException();
        }

        $response = new JsonResponse($this->groupFactory->create($magazine));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}

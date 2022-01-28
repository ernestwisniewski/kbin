<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Contracts\ContentInterface;
use App\Service\CardanoManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class EntryCardanoTxInitController extends AbstractController
{
    public function __construct(private CardanoManager $manager, private RequestStack $requestStack)
    {
    }

    public function __invoke(ContentInterface $subject, Request $request): Response
    {
        $this->manager->txInit($subject, $this->requestStack->getSession()->getId(), $this->getUser());

        return new JsonResponse(
            [
                'success' => true,
            ]
        );
    }
}

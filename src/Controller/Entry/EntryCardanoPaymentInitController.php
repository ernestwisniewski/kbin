<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Contracts\ContentInterface;
use App\Service\CardanoManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCardanoPaymentInitController extends AbstractController
{
    public function __invoke(ContentInterface $subject, Request $request, CardanoManager $manager): Response
    {
        $manager->paymentInit($subject, $this->getUser());

        return new JsonResponse(
            [
                'success' => true,
            ]
        );
    }
}

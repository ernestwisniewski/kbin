<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Service\CardanoManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoWalletCreateController extends CardanoController
{
    public function __invoke(CardanoManager $wallet, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse($wallet->createWallet($this->getUserOrThrow()));
        } else {
            $response = $this->render('cardano/create_wallet.html.twig', $wallet->createWallet($this->getUserOrThrow()));
        }

        return $this->send($response);
    }
}

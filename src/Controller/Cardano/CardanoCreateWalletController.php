<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Cardano\CardanoWallet;
use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoCreateWalletController extends AbstractController
{
    public function __invoke(CardanoWallet $wallet, Request $request): Response
    {
        return new JsonResponse(
            [
                'mnemonic' => $wallet->create(),
            ]
        );
    }
}

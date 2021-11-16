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
        $response = new JsonResponse(
            [
                'mnemonic' => $wallet->create(),
            ]
        );

        return $this->send($response);
    }

    private function send(Response $response): Response
    {
        $response->setCache([
            'must_revalidate' => true,
            'no_cache' => true,
            'no_store' => true,
            'no_transform' => true,
            'private' => true,
            'proxy_revalidate' => true,
        ]);

        return $response;
    }
}

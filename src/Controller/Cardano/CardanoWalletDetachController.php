<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Service\CardanoManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoWalletDetachController extends CardanoController
{
    public function __invoke(CardanoManager $wallet, Request $request): Response
    {
        $wallet->detachWallet($this->getUserOrThrow());

        return $this->redirectToRefererOrHome($request);
    }
}

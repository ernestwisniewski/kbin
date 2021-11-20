<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Service\CardanoManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoWalletMnemonicController extends CardanoController
{
    public function __invoke(CardanoManager $wallet, Request $request): Response
    {
        return $this->redirectToRefererOrHome($request);
    }
}

<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Service\CardanoManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoTransactionFeeController extends CardanoController
{
    public function __invoke(CardanoManager $manager, Request $request): Response
    {
        $req = $request->toArray();
        $fee = $manager->calculateFee($req['address'], $req['walletId'], (float) $req['amount']);

        return new JsonResponse(['success' => true, 'fee' => $fee['fee'], 'sum' => $fee['sum']]);
    }
}

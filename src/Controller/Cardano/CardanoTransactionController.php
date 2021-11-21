<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Cardano\CardanoWalletTransactions;
use App\Form\CardanoTransactionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoTransactionController extends CardanoController
{
    public function __invoke(CardanoWalletTransactions $wallet, Request $request): Response
    {
        $form = $this->createForm(CardanoTransactionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto  = $form->getData();
            $user = $this->getUserOrThrow();
            $tx   = $wallet->create($user->getPassword(), $user->cardanoWalletId, $dto->walletAddress, $dto->amount);
        }

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(['success' => true, 'transaction' => $tx ?? null]);
        } else {
            return $this->redirectToRefererOrHome($request);
        }

        return $this->send($response);
    }
}

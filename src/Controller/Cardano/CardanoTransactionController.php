<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Cardano\CardanoTransactions;
use App\Form\CardanoTransactionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoTransactionController extends CardanoController
{
    public function __invoke(CardanoTransactions $wallet, Request $request): Response
    {
        $form = $this->createForm(CardanoTransactionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto  = $form->getData();
            $user = $this->getUserOrThrow();
            dd($wallet->create($user->getPassword(), $user->cardanoWalletId, $dto->walletAddress, $dto->amount));
        }

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(['success' => true]);
        } else {
            return $this->redirectToRefererOrHome($request);
        }

        return $this->send($response);
    }
}

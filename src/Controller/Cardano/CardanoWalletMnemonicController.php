<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Form\CardanoMnemonicType;
use App\Service\CardanoManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoWalletMnemonicController extends CardanoController
{
    public function __invoke(CardanoManager $wallet, Request $request): Response
    {
        $form = $this->createForm(CardanoMnemonicType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wallet->createWallet($this->getUserOrThrow(), $form->getData()->mnemonic);
        }

        return $this->redirectToRefererOrHome($request);
    }
}

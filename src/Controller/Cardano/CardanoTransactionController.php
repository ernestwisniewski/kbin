<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Entity\Contracts\ContentInterface;
use App\Form\CardanoTransactionType;
use App\Service\CardanoManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoTransactionController extends CardanoController
{
    public function __invoke(ContentInterface $subject, CardanoManager $manager, Request $request): Response
    {
        $form = $this->createForm(CardanoTransactionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            $user = $this->getUserOrThrow();

            $tx = $manager->createTransaction(
                $this->getUserOrThrow(),
                $subject,
                $user->getPassword(),
                $user->cardanoWalletId,
                $dto->walletAddress,
                $dto->amount
            );
        }

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(['success' => true, 'transaction' => $tx ?? null]);
        } else {
            return $this->redirectToRefererOrHome($request);
        }

        return $this->send($response);
    }
}

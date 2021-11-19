<?php declare(strict_types=1);

namespace App\Controller\Cardano;

use App\Cardano\CardanoTransactions;
use App\Controller\AbstractController;
use App\Form\CardanoTransactionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CardanoTransactionController extends AbstractController
{
    public function __invoke(CardanoTransactions $wallet, Request $request): Response
    {
        $form = $this->createForm(CardanoTransactionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $wallet->create($dto->mnemonic, $dto->walletAddress, $dto->amount);
        }

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(['success' => true]);
        } else {
            return $this->redirectToRefererOrHome($request);
        }

        return $this->send($response);
    }

    private function send(Response $response): Response
    {
        $response->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => true,
            'private'          => true,
            'proxy_revalidate' => true,
        ]);

        return $response;
    }
}

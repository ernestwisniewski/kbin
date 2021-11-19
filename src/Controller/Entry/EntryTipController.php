<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\DTO\CardanoTransactionDto;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Form\CardanoTransactionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryTipController extends AbstractController
{
    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     */
    public function __invoke(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $dto = new CardanoTransactionDto();
        $dto->walletAddress = $entry->user->cardanoWalletAddress;

        $form = $this->createForm(CardanoTransactionType::class, $dto, [
            'action' => $this->generateUrl('cardano_transaction'),
        ]);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('_layout/_tips.html.twig', [
                    'subject'      => $entry,
                    'key'          => 'entry',
                    'form'         => $form->createView(),
                    'transactions' => [],
                ]),
            ]);
        }

        return $this->render('entry/tips.html.twig', [
            'magazine'     => $magazine,
            'entry'        => $entry,
            'form'         => $form->createView(),
            'transactions' => [],
        ]);
    }
}

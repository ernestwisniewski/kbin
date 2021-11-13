<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
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
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('_layout/_tips.html.twig', [
                    'magazine'     => $magazine,
                    'entry'        => $entry,
                    'transactions' => [],
                ]),
            ]);
        }

        return $this->render('entry/tips.html.twig', [
            'magazine'     => $magazine,
            'entry'        => $entry,
            'transactions' => [],
        ]);
    }
}

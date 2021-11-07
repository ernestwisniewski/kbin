<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class EntryVotersController extends AbstractController
{
    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     */
    public function __invoke(Magazine $magazine, Entry $entry): Response
    {
        return $this->render('entry/voters.html.twig', [
            'magazine' => $magazine,
            'entry'    => $entry,
            'votes'    => $entry->votes,
        ]);
    }
}

<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;

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

    public function shortList(
        Magazine $magazine,
        Entry $entry,
    ): Response {
        $cache = new FilesystemAdapter();

        $id       = $entry->getId();
        $showMore = $entry->votes->count() > 5;

        if ($showMore) {
            return $cache->get("voters_entry_$id", function (ItemInterface $item) use ($magazine, $entry) {
                $item->expiresAfter(3600);

                return $this->getResponse($magazine, $entry);
            });
        }

        return $this->getResponse($magazine, $entry);
    }

    private function getResponse(Magazine $magazine, Entry $entry): Response
    {
        return $this->render(
            'entry/_voters_list_sidebar.html.twig',
            [
                'magazine'  => $magazine,
                'entry'     => $entry,
                'votes'     => $entry->votes->slice(0, 5),
                'show_more' => true,
            ]
        );
    }
}

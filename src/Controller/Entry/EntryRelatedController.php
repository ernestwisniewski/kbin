<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Service\SearchManager;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;

class EntryRelatedController extends AbstractController
{
    public function __construct(private SearchManager $manager)
    {
    }

    public function __invoke(
        Magazine $magazine,
        Entry $entry,
    ): Response {
        $cache = new FilesystemAdapter();

        $id = $entry->getId();
        return $cache->get("related_$id", function (ItemInterface $item) use ($entry, $magazine) {
            try {
                $entries = $this->manager->findRelated($entry->title.' '.$magazine->name);
            } catch (\Exception $e) {
                return new Response('');
            }

            $entries = array_filter($entries, fn($e) => $e->getId() !== $entry->getId());

            return $this->render('entry/_related.html.twig', ['entries' => $entries]);
        });
    }
}

<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Entry;
use App\Service\SearchManager;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('related_entries')]
class RelatedEntriesComponent
{
    public Entry $entry;

    public function __construct(private SearchManager $manager, private Environment $twig, private CacheInterface $cache)
    {
    }

    public function getHtml(): string
    {
        $id = $this->entry->getId();

        return $this->cache->get("related_$id", function (ItemInterface $item) {
            $item->expiresAfter(3600);

            try {
                $entries = $this->manager->findRelated($this->entry->title.' '.$this->entry->magazine->name);
                $entries = is_array($entries) ? array_filter($entries, fn($e) => $e->getId() !== $this->entry->getId()) : [];

                if (!count($entries)) {
                    throw new \Exception('Empty related entries list.');
                }
            } catch (\Exception $e) {
                return '';
            }

            return $this->twig->render('entry/_related.html.twig', ['entries' => $entries]);
        });
    }
}

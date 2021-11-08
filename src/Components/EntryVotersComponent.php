<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Entry;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('entry_voters')]
class EntryVotersComponent
{
    public Entry $entry;
    public bool $showMore = false;

    public function __construct(private Environment $twig)
    {
    }

    public function getHtml(): string
    {
        if ($this->entry->votes->isEmpty()) {
            return '';
        }

        $cache = new FilesystemAdapter();

        $id             = $this->entry->getId();
        $this->showMore = $this->entry->votes->count() > 5;

        if ($this->showMore) {
            return $cache->get("voters_entry_$id", function (ItemInterface $item) {
                $item->expiresAfter(3600);

                return $this->render();
            });
        }

        return $this->render();
    }

    private function render(): string
    {
        return $this->twig->render(
            'entry/_voters_list_sidebar.html.twig',
            [
                'magazine'  => $this->entry->magazine,
                'entry'     => $this->entry,
                'votes'     => $this->entry->votes->slice(0, 5),
                'show_more' => $this->showMore,
            ]
        );
    }
}

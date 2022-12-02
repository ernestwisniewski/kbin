<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Contracts\VoteInterface;
use App\Service\CacheService;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('favourites_list_short')]
class FavouritesListShortComponent
{
    public VoteInterface|FavouriteInterface $subject;
    public string $url;
    public ?int $more = null;

    public function __construct(
        private Environment $twig,
        private CacheService $cacheService,
        private CacheInterface $cache
    ) {
    }

    public function getHtml(): string
    {
        if ($this->subject->favourites->isEmpty()) {
            return '';
        }

        $this->more = $this->subject->favourites->count() > 5 ? $this->subject->favourites->count() - 5 : null;

        return $this->cache->get(
            $this->cacheService->getFavouritesCacheKey($this->subject),
            function (ItemInterface $item) {
                $item->expiresAfter(3600);

                return $this->render();
            }
        );
    }

    private function render(): string
    {
        return $this->twig->render(
            '_layout/_favourites_list_short.html.twig',
            [
                'magazine' => $this->subject->magazine,
                'subject' => $this->subject,
                'favourites' => $this->subject->favourites->slice(0, 5),
                'url' => $this->url,
                'more' => $this->more,
            ]
        );
    }
}

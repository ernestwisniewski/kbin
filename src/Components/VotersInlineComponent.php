<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VoteInterface;
use App\Service\CacheService;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('voters_inline')]
class VotersInlineComponent
{
    public VoteInterface $subject;
    public string $url;
    public ?int $more = null;

    public function __construct(private Environment $twig, private CacheService $cacheService, private CacheInterface $cache)
    {
    }

    public function getHtml(): string
    {
        if ($this->subject->votes->isEmpty()) {
            return '';
        }

        $id = $this->subject->getId();
        $this->more = $this->subject->votes->count() >= 5 ? $this->subject->votes->count() - 5 : null;

        return $this->cache->get($this->cacheService->getVotersCacheKey($this->subject), function (ItemInterface $item) {
            $item->expiresAfter(3600);

            return $this->render();
        });
    }

    private function render(): string
    {
        return $this->twig->render(
            '_layout/_voters_inline.html.twig',
            [
                'votes' => $this->subject->votes->slice(0, 5),
                'more'  => $this->more,
                'url'   => $this->url,
            ]
        );
    }
}

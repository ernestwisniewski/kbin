<?php

declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VoteInterface;
use App\Entity\Vote;
use App\Service\CacheService;
use Doctrine\Common\Collections\Collection;
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

    public function __construct(
        private readonly Environment $twig,
        private readonly CacheService $cacheService,
        private readonly CacheInterface $cache
    ) {
    }

    public function getHtml(): string
    {
        if ($this->subject->votes->isEmpty()) {
            return '';
        }

        $this->more = $this->subject->votes->count() >= 4 ? $this->subject->votes->count() - 4 : null;

        return $this->cache->get(
            $this->cacheService->getVotersCacheKey($this->subject),
            function (ItemInterface $item) {
                $item->expiresAfter(3600);

                return $this->render();
            }
        );
    }

    private function render(): string
    {
        /**
         * @var Collection $votes
         */
        $votes = $this->subject->votes;
        $votes = $votes->filter(function ($vote) {
            /*
             * @var Vote $vote
             */
            return VoteInterface::VOTE_UP === $vote->choice;
        });

        return $this->twig->render(
            '_layout/_voters_inline.html.twig',
            [
                'votes' => $votes->slice(0, 4),
                'more' => $this->more,
                'url' => $this->url,
            ]
        );
    }
}

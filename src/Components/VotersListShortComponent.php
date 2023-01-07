<?php

declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VoteInterface;
use App\Entity\Vote;
use App\Service\CacheService;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('voters_list_short')]
class VotersListShortComponent
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

        $this->more = $this->subject->votes->count() > 5 ? $this->subject->votes->count() - 5 : null;

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
        $votes = $this->subject->votes->filter(function (Vote $v) {
            return VoteInterface::VOTE_NONE !== $v->choice;
        })->slice(0, 5);

        return $this->twig->render(
            '_layout/_voters_list_short.html.twig',
            [
                'magazine' => $this->subject->magazine,
                'subject' => $this->subject,
                'votes' => $votes,
                'url' => $this->url,
                'more' => $this->more,
            ]
        );
    }
}

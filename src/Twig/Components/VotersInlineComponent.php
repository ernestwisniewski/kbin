<?php

namespace App\Twig\Components;

use App\Entity\Contracts\VotableInterface;
use App\Service\CacheService;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('voters_inline', template: 'components/_cached.html.twig')]
final class VotersInlineComponent
{
    public VotableInterface $subject;
    public string $url;

    public function __construct(
        private readonly Environment $twig,
        private readonly CacheInterface $cache,
        private readonly CacheService $cacheService,
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        return $this->cache->get(
            $this->cacheService->getVotersCacheKey($this->subject),
            function (ItemInterface $item) use ($attributes) {
                $item->expiresAfter(3600);
                /**
                 * @var Collection $votes
                 */
                $votes = $this->subject->votes;
                $votes = $votes->matching(
                    new Criteria(
                        Criteria::expr()->eq('choice', VotableInterface::VOTE_UP),
                        ['createdAt' => Criteria::DESC]
                    )
                )->slice(0, 4);

                return $this->twig->render(
                    'components/voters_inline.html.twig',
                    [
                        'attributes' => new ComponentAttributes($attributes->all()),
                        'voters' => array_map(fn ($vote) => $vote->user->username, $votes),
                        'count' => $this->subject->countUpVotes(),
                        'url' => $this->url,
                    ]
                );
            }
        );
    }
}

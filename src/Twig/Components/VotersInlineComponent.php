<?php

namespace App\Twig\Components;

use App\Entity\Contracts\VotableInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
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
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        /**
         * @var Collection $votes
         */
        $votes = $this->subject->votes;
        $votes = $votes->matching(
            new Criteria(Criteria::expr()->eq('choice', VotableInterface::VOTE_UP), ['createdAt' => Criteria::DESC])
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
}

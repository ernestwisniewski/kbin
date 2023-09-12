<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('vote')]
final class VoteComponent
{
    public VotableInterface $subject;
    public string $formDest;
    public bool $showDownvote = true;

    #[PostMount]
    public function postMount(array $attr): array
    {
        $this->formDest = match (true) {
            $this->subject instanceof Entry => 'entry',
            $this->subject instanceof EntryComment => 'entry_comment',
            $this->subject instanceof Post => 'post',
            $this->subject instanceof PostComment => 'post_comment',
            default => throw new \LogicException(),
        };

        return $attr;
    }
}

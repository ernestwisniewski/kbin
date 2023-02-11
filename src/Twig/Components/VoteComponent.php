<?php

namespace App\Twig\Components;

use App\Entity\Contracts\VoteInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('vote')]
final class VoteComponent
{
    public VoteInterface $subject;
    public string $formDest;

    public bool $showDownvote = true;

    #[PostMount]
    public function postMount(array $attr): array
    {
        $this->formDest = match (true) {
            $this->subject instanceof Entry => 'entry_vote',
            $this->subject instanceof EntryComment => 'entry_comment_vote',
            $this->subject instanceof Post => 'post_vote',
            $this->subject instanceof PostComment => 'post_comment_vote',
            default => throw new \LogicException(),
        };

        return $attr;
    }
}

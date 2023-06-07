<?php

namespace App\Twig\Components;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('favourite')]
final class FavouriteComponent
{
    public string $formDest;
    public ContentInterface $subject;

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

<?php

namespace App\Twig\Components;

use App\Entity\EntryComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('entry_comments_nested', template: 'components/cached.html.twig')]
final class EntryCommentsNestedComponent
{
    public EntryComment $comment;
    public int $level;

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function getHtml(): string
    {
        return $this->render();
    }

    private function render(): string
    {
        return $this->twig->render(
            'components/entry_comments_nested.html.twig',
            [
                'comment' => $this->comment,
                'level' => $this->level,
            ]
        );
    }
}

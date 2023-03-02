<?php

namespace App\Twig\Components;

use App\Entity\PostComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('post_comments_nested', template: 'components/cached.html.twig')]
final class PostCommentsNestedComponent
{
    public PostComment $comment;
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
            'components/post_comments_nested.html.twig',
            [
                'comment' => $this->comment,
                'level' => $this->level,
            ]
        );
    }
}

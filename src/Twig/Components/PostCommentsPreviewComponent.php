<?php

namespace App\Twig\Components;

use App\Entity\Post;
use Symfony\Component\Security\Core\Security;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('post_comments_preview', template: 'components/cached.html.twig')]
final class PostCommentsPreviewComponent
{
    public Post $post;

    public function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
    ) {
    }

    public function getHtml(): string
    {
        return $this->render();
    }

    private function render(): string
    {
        return $this->twig->render(
            'components/post_comments_preview.html.twig',
            [
                'post' => $this->post,
                'comments' => $this->post->lastActive < (new \DateTime('-4 hours'))
                    ? $this->post->getBestComments($this->security->getUser())
                    : $this->post->getLastComments($this->security->getUser()),
            ]
        );
    }
}

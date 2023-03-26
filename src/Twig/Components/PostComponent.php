<?php

namespace App\Twig\Components;

use App\Entity\Post;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('post')]
final class PostComponent
{
    public Post $post;
    public bool $isSingle = false;
    public bool $showMagazineName = true;
    public bool $dateAsUrl = true;
    public bool $showCommentsPreview = false;
    public bool $showExpand = true;
    public bool $showModeratePanel = false;

    #[PostMount]
    public function postMount(array $attr): array
    {
        if ($this->isSingle) {
            $this->showMagazineName = false;

            if (isset($attr['class'])) {
                $attr['class'] = trim('post--single '.$attr['class']);
            } else {
                $attr['class'] = 'post--single';
            }
        }

        return $attr;
    }
}

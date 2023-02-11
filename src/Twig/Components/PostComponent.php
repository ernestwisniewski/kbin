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

    #[PostMount]
    public function postMount(array $attr): array
    {
        if ($this->isSingle) {
            if (isset($attr['class'])) {
                $attr['class'] = trim('kbin-post--single '.$attr['class']);
            } else {
                $attr['class'] = 'kbin-post--single';
            }
        }

        return $attr;
    }
}

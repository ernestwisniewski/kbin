<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Post;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('post_comments_preview')]
class PostCommentsPreviewComponent
{
    public Post $post;

    public function __construct(private Environment $twig, private CacheInterface $cache)
    {
    }

    public function getHtml(): string
    {
        $id = $this->post->getId();

        return $this->cache->get("comments_preview_post_$id", function (ItemInterface $item) {
            $item->expiresAfter(0);

            return $this->twig->render(
                'post/comment/_list.html.twig',
                [
                    'post' => $this->post,
                    'comments' => $this->post->lastActive < (new \DateTime('-6 hours'))
                        ? $this->post->getBestComments()
                        : $this->post->getLastComments(),
                ]
            );
        });
    }
}

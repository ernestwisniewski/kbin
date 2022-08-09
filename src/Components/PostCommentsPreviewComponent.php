<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Post;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('post_comments_preview')]
class PostCommentsPreviewComponent
{
    public Post $post;

    public function __construct(private Environment $twig, private CacheInterface $cache, private Security $security)
    {
    }

    public function getHtml(): string
    {
        $post = $this->post->getId();
        $user = $this->security->getUser()?->getId();

        return $this->cache->get('preview_post_comment_'.$post.'_'.$user, function (ItemInterface $item) use ($post, $user) {
            $item->expiresAfter(3600);
            $item->tag(['post_comments_user_'.$user]);
            $item->tag(['post_'.$post]);

            return $this->twig->render(
                'post/comment/_list.html.twig',
                [
                    'post' => $this->post,
                    'comments' => $this->post->lastActive < (new \DateTime('-4 hours'))
                        ? $this->post->getBestComments()
                        : $this->post->getLastComments(),
                ]
            );
        });
    }
}

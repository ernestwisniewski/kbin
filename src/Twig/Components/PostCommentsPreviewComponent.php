<?php

namespace App\Twig\Components;

use App\Entity\Post;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('post_comments_preview', template: 'components/_cached.html.twig')]
final class PostCommentsPreviewComponent
{
    public Post $post;

    public function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $postId = $this->post->getId();
        $userId = $this->security->getUser()?->getId();

        $cache = new FilesystemTagAwareAdapter();

        return $cache->get(
            "post_comment_preview_{$postId}_{$userId}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($postId, $userId, $attributes) {
                $item->expiresAfter(3600);
                $item->tag(['post_comments_user_'.$userId]);
                $item->tag(['post_'.$postId]);

                return $this->twig->render(
                    'components/post_comments_preview.html.twig',
                    [
                        'attributes' => new ComponentAttributes($attributes->all()),
                        'post' => $this->post,
                        'comments' => $this->post->lastActive < (new \DateTime('-4 hours'))
                            ? $this->post->getBestComments($this->security->getUser())
                            : $this->post->getLastComments($this->security->getUser()),
                    ]
                );
            }
        );
    }
}

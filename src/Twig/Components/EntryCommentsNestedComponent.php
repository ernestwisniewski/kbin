<?php

namespace App\Twig\Components;

use App\Entity\EntryComment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('entry_comments_nested', template: 'components/_cached.html.twig')]
final class EntryCommentsNestedComponent
{
    public EntryComment $comment;
    public int $level;

    public function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $commentId = $this->comment->root?->getId() ?? $this->comment->getId();
        $userId = $this->security->getUser()?->getId();

        return $this->cache->get(
            "entry_comment_nested_{$commentId}_{$userId}",
            function (ItemInterface $item) use ($commentId, $userId) {
                $item->expiresAfter(3600);
                $item->tag(['entry_comments_user_'.$userId]);
                $item->tag(['entry_comment_'.$commentId]);

                return $this->twig->render(
                    'components/entry_comments_nested.html.twig',
                    [
                        'comment' => $this->comment,
                        'level' => $this->level,
                    ]
                );
            }
        );
    }
}

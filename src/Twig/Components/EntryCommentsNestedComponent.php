<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\EntryComment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
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
    public string $view = ThemeSettingsController::TREE;

    public function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $commentId = $this->comment->root?->getId() ?? $this->comment->getId();
        $userId = $this->security->getUser()?->getId();

        return $this->cache->get(
            "entry_comments_nested_{$commentId}_{$userId}_{$this->view}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($commentId, $userId) {
                $item->expiresAfter(3600);
                $item->tag(['entry_comments_user_'.$userId]);
                $item->tag(['entry_comment_'.$commentId]);

                return $this->twig->render(
                    'components/entry_comments_nested.html.twig',
                    [
                        'comment' => $this->comment,
                        'level' => $this->level,
                        'view' => $this->view,
                    ]
                );
            }
        );
    }
}

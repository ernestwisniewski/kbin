<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\EntryComment;
use App\Repository\EntryCommentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('entry_comments_nested', template: 'components/_cached.html.twig')]
final class EntryCommentsNestedComponent
{
    public EntryComment $comment;
    public array $nestedComments = [];
    public int $level;
    public string $view = ThemeSettingsController::TREE;

    public function __construct(
        private readonly EntryCommentRepository $entryCommentRepository,
        private readonly Environment $twig,
        private readonly Security $security,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack,

    ) {
    }


    #[PostMount]
    public function postMount(array $attr): array
    {
        if (null === $this->comment->root) {
            $this->nestedComments = $this->entryCommentRepository->findAllChildren( $this->comment);
        }

        return $attr;
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        if ($this->security->getUser()) {
            return $this->renderView();
        }

        $commentId = $this->comment->root?->getId() ?? $this->comment->getId();
        $userId = $this->security->getUser()?->getId();

        return $this->cache->get(
            "entry_comments_nested_{$commentId}_{$userId}_{$this->view}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($commentId, $userId) {
                $item->expiresAfter(300);
                $item->tag(['entry_comments_user_'.$userId]);
                $item->tag(['entry_comment_'.$commentId]);
                $item->tag(['user_view_'.$userId]);

                return $this->renderView();
            }
        );
    }

    private function renderView(): string
    {
        return $this->twig->render(
            'components/entry_comments_nested.html.twig',
            [
                'comment' => $this->comment,
                'nestedComments' => $this->nestedComments,
                'level' => $this->level,
                'view' => $this->view,
            ]
        );
    }
}

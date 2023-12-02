<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Kbin\NewCommentMarker\NewCommentMarkerLastSeen;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

class SubjectExtension extends AbstractExtension
{
    public function __construct(private NewCommentMarkerLastSeen $newCommentMarkerLastSeen)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_new_comment', [$this, 'isNewComment']),
        ];
    }

    public function isNewComment(?User $user, EntryComment|PostComment $subject): bool
    {
        if (!$user) {
            return false;
        }

        $parent = $subject->getParentSubject();

        $lastSeen = ($this->newCommentMarkerLastSeen)($user, $parent);

        if (null === $lastSeen) {
            return false;
        }

        if ($lastSeen < $subject->createdAt) {
            return true;
        }

        return false;
    }

    public function getTests(): array
    {
        return [
            new TwigTest(
                'entry', function ($subject) {
                    return $subject instanceof Entry;
                }
            ),
            new TwigTest(
                'entry_comment', function ($subject) {
                    return $subject instanceof EntryComment;
                }
            ),
            new TwigTest(
                'post', function ($subject) {
                    return $subject instanceof Post;
                }
            ),
            new TwigTest(
                'post_comment', function ($subject) {
                    return $subject instanceof PostComment;
                }
            ),
            new TwigTest(
                'magazine', function ($subject) {
                    return $subject instanceof Magazine;
                }
            ),
        ];
    }
}

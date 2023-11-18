<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('boost')]
final class BoostComponent
{
    public string $formDest;
    public ContentInterface $subject;

    #[PostMount]
    public function postMount(array $attr): array
    {
        $this->formDest = match (true) {
            $this->subject instanceof Entry => 'entry',
            $this->subject instanceof EntryComment => 'entry_comment',
            $this->subject instanceof Post => 'post',
            $this->subject instanceof PostComment => 'post_comment',
            default => throw new \LogicException(),
        };

        return $attr;
    }
}

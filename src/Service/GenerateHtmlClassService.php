<?php

namespace App\Service;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;

class GenerateHtmlClassService
{
    public function fromEntity(ContentInterface $subject): string
    {
        return match (true) {
            $subject instanceof Entry => "entry-{$subject->getId()}",
            $subject instanceof EntryComment => "entry-comment-{$subject->getId()}",
            $subject instanceof Post => "post-{$subject->getId()}",
            $subject instanceof PostComment => "post-comment-{$subject->getId()}",
            default => throw new \LogicException(),
        };
    }

    public function fromClassName(string $class, int $id): string
    {
        return match ($class) {
            'Entry' => "entry-{$id}",
            'EntryComment' => "entry-comment-{$id}",
            'Post' => "post-{$id}",
            'PostComment' => "post-comment-{$id}",
            default => throw new \LogicException(),
        };
    }
}

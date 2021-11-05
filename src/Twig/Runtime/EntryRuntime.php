<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\Entry;
use App\Entity\EntryComment;
use Twig\Extension\RuntimeExtensionInterface;

class EntryRuntime implements RuntimeExtensionInterface
{
    public function getAuthorEntryComment(Entry $entry): ?EntryComment
    {
        $comment = $entry->comments->first();
        if ($comment && $entry->isAuthor($comment->user)) {
            return $comment;
        }

        return null;
    }
}


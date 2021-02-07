<?php declare(strict_types=1);

namespace App\PageView;

use App\Entity\Entry;
use App\Repository\Criteria;

class EntryCommentPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_NEW,
        self::SORT_TOP,
    ];

    private ?Entry $entry = null;
    private bool $onlyParents = true;

    public function isOnlyParents(): bool
    {
        return $this->onlyParents;
    }

    public function showOnlyParents(bool $onlyParents): self
    {
        $this->onlyParents = $onlyParents;

        return $this;
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    public function showEntry(Entry $entry): self
    {
        $this->entry = $entry;

        return $this;
    }
}

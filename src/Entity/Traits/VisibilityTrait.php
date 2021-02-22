<?php

namespace App\Entity\Traits;

use App\Entity\Contracts\VisibilityInterface;

trait VisibilityTrait {
    abstract public function getVisibility(): string;

    public function isVisible(): bool {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_VISIBLE;
    }

    public function isSoftDeleted(): bool {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_SOFT_DELETED;
    }

    public function isTrashed(): bool {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_TRASHED;
    }
}

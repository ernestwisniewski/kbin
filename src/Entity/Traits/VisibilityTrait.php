<?php

namespace App\Entity\Traits;

use App\Entity\Contracts\VisibilityInterface;
use Doctrine\ORM\Mapping as ORM;

trait VisibilityTrait
{
    /**
     * @ORM\Column(type="text", options={"default": "visible"})
     */
    private $visibility = self::VISIBILITY_VISIBLE;

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function isVisible(): bool
    {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_VISIBLE;
    }

    public function isSoftDeleted(): bool
    {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_SOFT_DELETED;
    }

    public function isTrashed(): bool
    {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_TRASHED;
    }

    public function isPrivate(): bool
    {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_PRIVATE;
    }
}

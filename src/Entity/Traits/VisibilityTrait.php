<?php declare(strict_types = 1);

namespace App\Entity\Traits;

use App\Entity\Contracts\VisibilityInterface;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

trait VisibilityTrait
{
    /**
     * @ORM\Column(type="text", options={"default": "visible"})
     */
    public string $visibility = VisibilityInterface::VISIBILITY_VISIBLE;

    #[Pure] public function isVisible(): bool
    {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_VISIBLE;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    #[Pure] public function isSoftDeleted(): bool
    {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_SOFT_DELETED;
    }

    #[Pure] public function isTrashed(): bool
    {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_TRASHED;
    }

    #[Pure] public function isPrivate(): bool
    {
        return $this->getVisibility() === VisibilityInterface::VISIBILITY_PRIVATE;
    }
}

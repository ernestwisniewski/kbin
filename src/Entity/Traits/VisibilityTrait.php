<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use App\Entity\Contracts\VisibilityInterface;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

trait VisibilityTrait
{
    #[ORM\Column(type: 'string', length: 20, options: [
        'fixed' => true,
        'default' => VisibilityInterface::VISIBILITY_VISIBLE,
    ])]
    public string $visibility = VisibilityInterface::VISIBILITY_VISIBLE;

    #[Pure]
    public function isVisible(): bool
    {
        return VisibilityInterface::VISIBILITY_VISIBLE === $this->getVisibility();
    }

    public function getVisibility(): string
    {
        return trim($this->visibility);
    }

    #[Pure]
    public function isSoftDeleted(): bool
    {
        return VisibilityInterface::VISIBILITY_SOFT_DELETED === $this->getVisibility();
    }

    #[Pure]
    public function isTrashed(): bool
    {
        return VisibilityInterface::VISIBILITY_TRASHED === $this->getVisibility();
    }

    #[Pure]
    public function isPrivate(): bool
    {
        return VisibilityInterface::VISIBILITY_PRIVATE === $this->getVisibility();
    }
}

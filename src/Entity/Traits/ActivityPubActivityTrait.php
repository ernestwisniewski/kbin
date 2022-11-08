<?php declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ActivityPubActivityTrait
{
    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    public ?string $apId = null;
}

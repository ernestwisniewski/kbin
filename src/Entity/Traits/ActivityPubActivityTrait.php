<?php declare(strict_types = 1);

namespace App\Entity\Traits;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use function Symfony\Component\String\u;

trait ActivityPubActivityTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public ?string $apId = null;
}

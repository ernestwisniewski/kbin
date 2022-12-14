<?php declare(strict_types=1);

namespace App\Entity\Traits;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait EditedAtTrait
{
    #[ORM\Column(type: 'datetimetz_immutable', nullable: true)]
    public ?DateTimeImmutable $editedAt = null;

    public function getEditedAt(): DateTimeImmutable
    {
        return $this->editedAt;
    }
}

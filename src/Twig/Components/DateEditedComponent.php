<?php

namespace App\Twig\Components;

use App\Entity\Entry;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('date_edited')]
final class DateEditedComponent
{
    public \DateTimeInterface $createdAt;
    public ?\DateTimeInterface $editedAt = null;
}

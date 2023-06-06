<?php

namespace App\Twig\Components;

use App\Entity\Contracts\ContentInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('boost')]
final class BoostComponent
{
    public string $path;
    public ContentInterface $subject;
}

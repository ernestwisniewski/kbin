<?php

namespace App\Twig\Components;

use App\Entity\Contracts\VoteInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('vote')]
final class VoteComponent
{
    public VoteInterface $subject;
}

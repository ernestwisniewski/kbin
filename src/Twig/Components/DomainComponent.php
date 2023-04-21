<?php

namespace App\Twig\Components;

use App\Entity\Domain;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('domain')]
final class DomainComponent
{
    public Domain $domain;
}

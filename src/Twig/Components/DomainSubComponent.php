<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Domain;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('domain_sub')]
final class DomainSubComponent
{
    public Domain $domain;
}

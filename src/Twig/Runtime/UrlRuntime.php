<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\Entry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class UrlRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function entryUrl(Entry $entry): string
    {
        return $this->urlGenerator->generate('entry_single', [
            'magazine_name' => $entry->magazine->name,
            'entry_id' => $entry->getId(),
            'slug' => $entry->slug,
        ]);
    }
}

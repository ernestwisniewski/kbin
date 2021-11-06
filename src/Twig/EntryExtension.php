<?php declare(strict_types = 1);

namespace App\Twig;

use App\Twig\Runtime\EntryRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EntryExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_author_entry_comment', [EntryRuntime::class, 'getAuthorEntryComment']),
        ];
    }
}

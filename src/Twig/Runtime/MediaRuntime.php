<?php declare(strict_types = 1);

namespace App\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class MediaRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private string $storageUrl
    ) {
    }

    public function getPublicPath(string $path): string
    {
        return 'https://media.karab.in/'.$path;
    }
}

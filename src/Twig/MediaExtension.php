<?php declare(strict_types = 1);

namespace App\Twig;

use App\Twig\Runtime\MediaRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MediaExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [MediaRuntime::class, 'getPublicPath']),
        ];
    }

}

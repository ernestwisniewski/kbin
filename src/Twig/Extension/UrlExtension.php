<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\UrlExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UrlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('entry_url', [UrlExtensionRuntime::class, 'entryUrl']),
            new TwigFunction('entry_favourites_url', [UrlExtensionRuntime::class, 'entryFavouritesUrl']),
            new TwigFunction('entry_voters_url', [UrlExtensionRuntime::class, 'entryVotersUrl']),
            new TwigFunction('entry_comment_reply_url', [UrlExtensionRuntime::class, 'entryCommentReplyUrl']),
            new TwigFunction('post_url', [UrlExtensionRuntime::class, 'postUrl']),
        ];
    }
}

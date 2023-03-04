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
            new TwigFunction('entry_edit_url', [UrlExtensionRuntime::class, 'entryEditUrl']),
            new TwigFunction('entry_moderate_url', [UrlExtensionRuntime::class, 'entryModerateUrl']),
            new TwigFunction('entry_delete_url', [UrlExtensionRuntime::class, 'entryDeleteUrl']),
            new TwigFunction('entry_comment_create_url', [UrlExtensionRuntime::class, 'entryCommentCreateUrl']),
            new TwigFunction('post_url', [UrlExtensionRuntime::class, 'postUrl']),
            new TwigFunction('post_voters_url', [UrlExtensionRuntime::class, 'postVotersUrl']),
            new TwigFunction('post_favourites_url', [UrlExtensionRuntime::class, 'postFavouritesUrl']),
            new TwigFunction('post_comment_create_url', [UrlExtensionRuntime::class, 'postCommentReplyUrl']),
            new TwigFunction('options_url', [UrlExtensionRuntime::class, 'optionsUrl']),
        ];
    }
}

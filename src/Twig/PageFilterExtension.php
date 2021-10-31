<?php declare(strict_types = 1);

namespace App\Twig;

use App\Twig\Runtime\PageContextRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PageFilterExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_active_sort_option', [PageContextRuntime::class, 'isActiveSortOption']),
            new TwigFunction('get_active_sort_option', [PageContextRuntime::class, 'getActiveSortOption']),
            new TwigFunction('get_active_time_option', [PageContextRuntime::class, 'getActiveTimeOption']),
            new TwigFunction('get_active_type_option', [PageContextRuntime::class, 'getActiveTypeOption']),
            new TwigFunction('get_active_sort_option_path', [PageContextRuntime::class, 'getActiveSortOptionPath']),
            new TwigFunction('is_active_comment_filter', [PageContextRuntime::class, 'isActiveCommentFilter']),
            new TwigFunction('get_active_comment_filter_path', [PageContextRuntime::class, 'getActiveCommentFilterPath']),
        ];
    }
}

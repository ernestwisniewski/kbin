<?php declare(strict_types=1);

namespace App\Twig;

use App\Twig\Runtime\PageContextRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PageContextExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_homepage', [PageContextRuntime::class, 'isHomePage']),
            new TwigFunction('is_sub_page', [PageContextRuntime::class, 'isSubPage']),
            new TwigFunction('is_mod_page', [PageContextRuntime::class, 'isModPage']),
            new TwigFunction('is_magazine_page', [PageContextRuntime::class, 'isMagazinePage']),
            new TwigFunction('is_entry_page', [PageContextRuntime::class, 'isEntryPage']),
            new TwigFunction('is_user_page', [PageContextRuntime::class, 'isUserPage']),
            new TwigFunction('is_user_profile_page', [PageContextRuntime::class, 'isUserProfilePage']),
            new TwigFunction('is_current_magazine_page', [PageContextRuntime::class, 'isCurrentMagazinePage']),
            new TwigFunction('is_active_sort_option', [PageContextRuntime::class, 'isActiveSortOption']),
            new TwigFunction('get_active_sort_option', [PageContextRuntime::class, 'getActiveSortOption']),
            new TwigFunction('get_active_time_option', [PageContextRuntime::class, 'getActiveTimeOption']),
            new TwigFunction('get_active_type_option', [PageContextRuntime::class, 'getActiveTypeOption']),
            new TwigFunction('get_active_sort_option_path', [PageContextRuntime::class, 'getActiveSortOptionPath']),
            new TwigFunction('is_comments_page', [PageContextRuntime::class, 'isCommentsPage']),
            new TwigFunction('get_active_comments_page_path', [PageContextRuntime::class, 'getActiveCommentsPagePath']),
            new TwigFunction('is_active_comment_filter', [PageContextRuntime::class, 'isActiveCommentFilter']),
            new TwigFunction('get_active_comment_filter_path', [PageContextRuntime::class, 'getActiveCommentFilterPath']),
            new TwigFunction('is_posts_page', [PageContextRuntime::class, 'isPostsPage']),
            new TwigFunction('is_post_page', [PageContextRuntime::class, 'isPostPage']),
            new TwigFunction('is_report_page', [PageContextRuntime::class, 'isReportPage']),
            new TwigFunction('is_magazine_panel_page', [PageContextRuntime::class, 'isMagazinePanelPage']),
            new TwigFunction('get_active_posts_page_path', [PageContextRuntime::class, 'getActivePostsPagePath']),
            new TwigFunction('is_active_route', [PageContextRuntime::class, 'isActiveRoute']),
            new TwigFunction('is_route_contains', [PageContextRuntime::class, 'isRouteContains']),
        ];
    }
}

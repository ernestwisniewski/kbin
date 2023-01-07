<?php

declare(strict_types=1);

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
            new TwigFunction('is_front_page', [PageContextRuntime::class, 'isFrontPage']),
            new TwigFunction('is_sub_page', [PageContextRuntime::class, 'isSubPage']),
            new TwigFunction('is_mod_page', [PageContextRuntime::class, 'isModPage']),
            new TwigFunction('is_fav_page', [PageContextRuntime::class, 'isFavPage']),
            new TwigFunction('is_magazine_page', [PageContextRuntime::class, 'isMagazinePage']),
            new TwigFunction('is_entry_page', [PageContextRuntime::class, 'isEntryPage']),
            new TwigFunction('is_entries_page', [PageContextRuntime::class, 'isEntriesPage']),
            new TwigFunction('is_user_page', [PageContextRuntime::class, 'isUserPage']),
            new TwigFunction('is_tag_page', [PageContextRuntime::class, 'isTagPage']),
            new TwigFunction('is_domain_page', [PageContextRuntime::class, 'isDomainPage']),
            new TwigFunction('is_user_profile_page', [PageContextRuntime::class, 'isUserProfilePage']),
            new TwigFunction('is_comments_page', [PageContextRuntime::class, 'isCommentsPage']),
            new TwigFunction('get_active_comments_page_path', [PageContextRuntime::class, 'getActiveCommentsPagePath']),
            new TwigFunction('is_posts_page', [PageContextRuntime::class, 'isPostsPage']),
            new TwigFunction('is_post_page', [PageContextRuntime::class, 'isPostPage']),
            new TwigFunction('is_report_page', [PageContextRuntime::class, 'isReportPage']),
            new TwigFunction('is_magazine_panel_page', [PageContextRuntime::class, 'isMagazinePanelPage']),
            new TwigFunction('get_active_posts_page_path', [PageContextRuntime::class, 'getActivePostsPagePath']),
            new TwigFunction('is_active_route', [PageContextRuntime::class, 'isActiveRoute']),
            new TwigFunction('is_route_contains', [PageContextRuntime::class, 'isRouteContains']),
            new TwigFunction('is_route_start_with', [PageContextRuntime::class, 'isRouteStartWith']),
            new TwigFunction('get_sentences', [PageContextRuntime::class, 'getSentences']),
            new TwigFunction('is_active_stats_type', [PageContextRuntime::class, 'isActiveStatsType']),
            new TwigFunction('get_stats_page_path', [PageContextRuntime::class, 'getStatsPagePath']),
            new TwigFunction('is_active_reputation_type', [PageContextRuntime::class, 'isActiveReputationType']),
            new TwigFunction('get_reputation_page_path', [PageContextRuntime::class, 'getReputationPagePath']),
        ];
    }
}

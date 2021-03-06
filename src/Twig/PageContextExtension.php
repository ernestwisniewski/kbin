<?php declare(strict_types=1);

namespace App\Twig;

use App\Repository\PostRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use Twig\Extension\AbstractExtension;
use App\Repository\EntryRepository;
use App\Entity\Magazine;
use Twig\TwigFunction;

final class PageContextExtension extends AbstractExtension
{
    private RequestStack $requestStack;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_homepage', [$this, 'isHomePage']),
            new TwigFunction('is_sub_page', [$this, 'isSubPage']),
            new TwigFunction('is_magazine_page', [$this, 'isMagazinePage']),
            new TwigFunction('is_entry_page', [$this, 'isEntryPage']),
            new TwigFunction('is_user_page', [$this, 'isUserPage']),
            new TwigFunction('is_user_profile_page', [$this, 'isUserProfilePage']),
            new TwigFunction('is_current_magazine_page', [$this, 'isCurrentMagazinePage']),
            new TwigFunction('is_active_sort_option', [$this, 'isActiveSortOption']),
            new TwigFunction('get_active_sort_option', [$this, 'getActiveSortOption']),
            new TwigFunction('get_active_time_option', [$this, 'getActiveTimeOption']),
            new TwigFunction('get_active_sort_option_path', [$this, 'getActiveSortOptionPath']),
            new TwigFunction('is_comments_page', [$this, 'isCommentsPage']),
            new TwigFunction('get_active_comments_page_path', [$this, 'getActiveCommentsPagePath']),
            new TwigFunction('is_active_comment_filter', [$this, 'isActiveCommentFilter']),
            new TwigFunction('get_active_comment_filter_path', [$this, 'getActiveCommentFilterPath']),
            new TwigFunction('is_posts_page', [$this, 'isPostsPage']),
            new TwigFunction('is_post_page', [$this, 'isPostPage']),
            new TwigFunction('is_report_page', [$this, 'isReportPage']),
            new TwigFunction('get_active_posts_page_path', [$this, 'getActivePostsPagePath']),
            new TwigFunction('is_active_route', [$this, 'isActiveRoute']),
            new TwigFunction('is_route_contains', [$this, 'isRouteContains']),
        ];
    }

    public function isHomePage(): bool
    {
        return in_array($this->getCurrentRouteName(), ['front', 'entry_comments_front', 'posts_front']);
    }

    public function isSubPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'subscribed');
    }

    public function isCurrentMagazinePage(Magazine $magazine): bool
    {
        if (!$magazineRequest = $this->getCurrentRequest()->get('magazine')) {
            return false;
        }

        return $magazineRequest === $magazine;
    }

    public function isMagazinePage(): bool
    {
        if (in_array($this->getCurrentRouteName(), ['magazine_create', 'magazine_delete'])) {
            return false;
        }

        return (bool) $this->getCurrentRequest()->get('magazine');
    }

    public function isEntryPage(): bool
    {
        if (in_array($this->getCurrentRouteName(), ['entry_create', 'entry_purge'])) {
            return false;
        }

        return (bool) $this->getCurrentRequest()->get('entry');
    }

    public function isCommentsPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'comments');
    }

    public function isUserPage(): bool
    {
        return str_starts_with($this->getCurrentRouteName(), 'user');
    }

    public function isUserProfilePage(): bool
    {
        return str_starts_with($this->getCurrentRouteName(), 'user_profile');
    }

    public function isPostsPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'posts');
    }

    public function isPostPage(): bool
    {
        if (in_array($this->getCurrentRouteName(), ['post_create', 'post_purge'])) {
            return false;
        }

        return $this->getCurrentRouteName() === 'post_single';
    }

    public function isReportPage(): bool
    {
        return $this->isRouteContains('report');
    }

    public function isActiveSortOption($sortOption, $entriesOnly = true): bool
    {
        $default = EntryRepository::SORT_DEFAULT;

        if ($entriesOnly) {
            if ($this->isCommentsPage()) {
                $default = EntryCommentRepository::SORT_DEFAULT;
                return false;
            }

            if ($this->isPostsPage()) {
                $default = PostRepository::SORT_DEFAULT;
                return false;
            }

            if ($this->isEntryPage()) {
                return false;
            }

            if ($this->isUserPage()) {
                return false;
            }
        }

        return ($requestSort = $this->getCurrentRequest()->get('sortBy') ?? EntryRepository::SORT_DEFAULT) === $sortOption;
    }

    public function getActiveSortOption()
    {
        return $this->getCurrentRequest()->get('sortBy') ?? EntryRepository::SORT_DEFAULT;
    }

    public function getActiveTimeOption()
    {
        return $this->getCurrentRequest()->get('time') ?? EntryRepository::TIME_DEFAULT;
    }

    public function getActiveSortOptionPath(?string $sortOption = null, ?string $time = null, $entriesOnly = true): string
    {
        $routeName = 'front';

        if ($this->getCurrentRequest()->get('sortBy')) {
            $routeParams['sortBy'] = $this->getActiveSortOption();
        }
        if ($sortOption) {
            $routeParams = ['sortBy' => $sortOption];
        }

        if ($this->getCurrentRequest()->get('time')) {
            $routeParams['time'] = $this->getCurrentRequest()->get('time');
        }
        if ($time) {
            $routeParams['time'] = $time;
        }

        if (!$entriesOnly) {
            if ($this->isPostsPage()) {
                $routeName = 'posts_front';
            }

            if ($this->isCommentsPage()) {
                $routeName = 'entry_comments_front';
            }
        }

        if ($this->isMagazinePage()) {
            $magazine            = $this->getCurrentRequest()->get('magazine');
            $routeName           = 'front_magazine';
            $routeParams['name'] = $magazine->getName();

            if (!$entriesOnly) {
                if ($this->isPostsPage()) {
                    $routeName = 'magazine_posts';
                }

                if ($this->isCommentsPage()) {
                    $routeName = 'magazine_entry_comments';
                }
            }
        }

        if ($this->isSubPage()) {
            $routeName = 'front_subscribed';

            if (!$entriesOnly) {
                if ($this->isPostsPage()) {
                    $routeName = 'posts_subscribed';
                }

                if ($this->isCommentsPage()) {
                    $routeName = 'entry_comments_subscribed';
                }
            }
        }


        return $this->urlGenerator->generate(
            $routeName,
            $routeParams
        );
    }

    public function getActiveCommentsPagePath()
    {
        $routeName   = 'entry_comments_front';
        $routeParams = ['sortBy' => EntryCommentRepository::SORT_DEFAULT];

        if ($this->isMagazinePage()) {
            $magazine = $this->getCurrentRequest()->get('magazine');

            $routeName           = 'magazine_entry_comments';
            $routeParams['name'] = $magazine->getName();
        }

        if ($this->isSubPage()) {
            $routeName = 'entry_comments_subscribed';
        }

        if ($time = $this->getCurrentRequest()->get('time')) {
            $routeParams['time'] = $time;
        }

        return $this->urlGenerator->generate(
            $routeName,
            $routeParams
        );
    }

    public function getActivePostsPagePath()
    {
        $routeName   = 'posts_front';
        $routeParams = ['sortBy' => PostRepository::SORT_DEFAULT];

        if ($this->isMagazinePage()) {
            $magazine = $this->getCurrentRequest()->get('magazine');

            $routeName           = 'magazine_posts';
            $routeParams['name'] = $magazine->getName();
        }

        if ($this->isSubPage()) {
            $routeName = 'posts_subscribed';
        }

        if ($time = $this->getCurrentRequest()->get('time')) {
            $routeParams['time'] = $time;
        }

        return $this->urlGenerator->generate(
            $routeName,
            $routeParams
        );
    }

    public function getActiveCommentFilterPath(string $sortOption): string
    {
        $routeParams = [
            'sortBy' => $sortOption ?? EntryCommentRepository::SORT_DEFAULT,
        ];

        $routeName = str_replace('entry_comment_edit', 'entry_comments_front', $this->getCurrentRouteName());

        if ($this->isMagazinePage()) {
            $routeParams['name'] = $this->getCurrentRequest()->get('magazine')->getName();
        }

        if ($this->isEntryPage()) {
            $routeParams['magazine_name'] = $this->getCurrentRequest()->get('magazine')->getName();
            $routeParams['entry_id']      = $this->getCurrentRequest()->get('entry')->getId();
            unset($routeParams['name']);
        }

        return $this->urlGenerator->generate($routeName, $routeParams);
    }

    public function isActiveRoute(string $routeName): bool
    {
        return $routeName === $this->getCurrentRouteName();
    }

    public function isRouteContains(string $val): bool
    {
        return str_contains($this->getCurrentRouteName(), $val);
    }

    public function isActiveCommentFilter(string $sortOption): bool
    {
        return ($this->getCurrentRequest()->get('sortBy') ?? EntryCommentRepository::SORT_DEFAULT) === $sortOption;
    }

    private function getCurrentRouteName(): string
    {
        return $this->getCurrentRequest()->get('_route');
    }

    private function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}

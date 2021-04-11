<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\Magazine;
use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class PageContextRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function isHomePage(): bool
    {
        return in_array($this->getCurrentRouteName(), ['front', 'entry_comments_front', 'posts_front']);
    }

    private function getCurrentRouteName(): string
    {
        return $this->getCurrentRequest()->get('_route');
    }

    private function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    public function isCurrentMagazinePage(Magazine $magazine): bool
    {
        if (!$magazineRequest = $this->getCurrentRequest()->get('magazine')) {
            return false;
        }

        return $magazineRequest === $magazine;
    }

    public function isUserProfilePage(): bool
    {
        return str_starts_with($this->getCurrentRouteName(), 'user_profile');
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

    public function isRouteContains(string $val): bool
    {
        return str_contains($this->getCurrentRouteName(), $val);
    }

    public function isMagazinePanelPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'magazine_panel');
    }

    public function isActiveSortOption($sortOption, $entriesOnly = true): bool
    {
        if ($entriesOnly) {
            if ($this->isCommentsPage()) {
                return false;
            }

            if ($this->isPostsPage()) {
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

    public function isCommentsPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'comments');
    }

    public function isPostsPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'posts');
    }

    public function isEntryPage(): bool
    {
        if (in_array($this->getCurrentRouteName(), ['entry_create', 'entry_purge'])) {
            return false;
        }

        return (bool) $this->getCurrentRequest()->get('entry');
    }

    public function isUserPage(): bool
    {
        return str_starts_with($this->getCurrentRouteName(), 'user');
    }

    public function getActiveTimeOption()
    {
        return $this->getCurrentRequest()->get('time') ?? EntryRepository::TIME_DEFAULT;
    }

    public function getActiveTypeOption(): ?string
    {
        return $this->getCurrentRequest()->get('typ', null);// @todo
    }

    public function getActiveSortOptionPath(?string $sortOption = null, ?string $time = null, ?string $type = null, $entriesOnly = true): string
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

        if ($this->getCurrentRequest()->get('typ')) {
            $routeParams['typ'] = $this->getCurrentRequest()->get('typ');
        }
        if ($type) {
            $routeParams['typ'] = $type;
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
            $routeParams['name'] = $magazine->name;

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

    public function getActiveSortOption()
    {
        return $this->getCurrentRequest()->get('sortBy') ?? EntryRepository::SORT_DEFAULT;
    }

    public function isMagazinePage(): bool
    {
        if (in_array($this->getCurrentRouteName(), ['magazine_create', 'magazine_delete'])) {
            return false;
        }

        return (bool) $this->getCurrentRequest()->get('magazine');
    }

    public function isSubPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'subscribed');
    }

    public function getActiveCommentsPagePath()
    {
        $routeName   = 'entry_comments_front';
        $routeParams = ['sortBy' => EntryCommentRepository::SORT_DEFAULT];

        if ($this->isMagazinePage()) {
            $magazine = $this->getCurrentRequest()->get('magazine');

            $routeName           = 'magazine_entry_comments';
            $routeParams['name'] = $magazine->name;
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
            $routeParams['name'] = $magazine->name;
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
            $routeParams['name'] = $this->getCurrentRequest()->get('magazine')->name;
        }

        if ($this->isEntryPage()) {
            $routeParams['magazine_name'] = $this->getCurrentRequest()->get('magazine')->name;
            $routeParams['entry_id']      = $this->getCurrentRequest()->get('entry')->getId();
            unset($routeParams['name']);
        }

        return $this->urlGenerator->generate($routeName, $routeParams);
    }

    public function isActiveRoute(string $routeName): bool
    {
        return $routeName === $this->getCurrentRouteName();
    }

    public function isActiveCommentFilter(string $sortOption): bool
    {
        return ($this->getCurrentRequest()->get('sortBy') ?? EntryCommentRepository::SORT_DEFAULT) === $sortOption;
    }
}

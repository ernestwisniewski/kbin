<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class PageContextRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator
    ) {
    }

    public function isStartPage(): bool
    {
        return $this->isRouteContains('front') || in_array($this->getCurrentRouteName(), ['magazine_entry_comments', 'magazine_posts']);
    }

    public function isHomePage(): bool
    {
        return in_array($this->getCurrentRouteName(), ['front', 'entry_comments_front', 'posts_front']);
    }

    public function isFrontPage(): bool
    {
        return $this->isRouteContains('front') || in_array($this->getCurrentRouteName(), ['magazine_entry_comments', 'magazine_posts']);
    }

    private function getCurrentRouteName(): string
    {
        return $this->getCurrentRequest()->get('_route') ?? 'front';
    }

    private function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
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

        return ($this->getCurrentRequest()->get('sortBy') ?? strtolower($this->translator->trans('sort.'.EntryRepository::SORT_DEFAULT)))
            === $sortOption;
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

    public function isTagPage(): bool
    {
        return str_starts_with($this->getCurrentRouteName(), 'tag');
    }

    public function isDomainPage(): bool
    {
        return str_starts_with($this->getCurrentRouteName(), 'domain');
    }


    public function getActiveTimeOption()
    {
        return $this->getCurrentRequest()->get('time') ?? EntryRepository::TIME_DEFAULT;
    }

    public function getActiveTypeOption(): ?string
    {
        return $this->getCurrentRequest()->get('type', null);// @todo
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

        if ($this->getCurrentRequest()->get('type')) {
            $routeParams['type'] = $this->getCurrentRequest()->get('type');
        }
        if ($type) {
            $routeParams['type'] = $type;
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

        if ($this->isModPage()) {
            $routeName = 'front_moderated';

            if (!$entriesOnly) {
                if ($this->isPostsPage()) {
                    $routeName = 'posts_moderated';
                }

                if ($this->isCommentsPage()) {
                    $routeName = 'entry_comments_moderated';
                }
            }
        }

        if ($this->isTagPage()) {
            $routeName           = 'tag_front';
            $routeParams['name'] = $this->getCurrentRequest()->get('name');


            if($this->isCommentsPage() && !$entriesOnly) {
                $routeName  = 'tag_entry_comments_front';
            }

            if($this->isPostsPage() && !$entriesOnly) {
                $routeName  = 'tag_posts_front';
            }
        }

        if ($this->isDomainPage()) {
            $routeName           = 'domain_front';
            $routeParams['name'] = $this->getCurrentRequest()->get('name');


            if($this->isCommentsPage() && !$entriesOnly) {
                $routeName  = 'domain_entry_comments_front';
            }
        }

        return $this->urlGenerator->generate(
            $routeName,
            $routeParams
        );
    }

    public function getActiveSortOption()
    {
        return $this->getCurrentRequest()->get('sortBy') ?? $this->translator->trans('sort.'.EntryRepository::SORT_DEFAULT);
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

    public function isModPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'moderated');
    }

    public function getActiveCommentsPagePath(): string
    {
        $routeName   = 'entry_comments_front';
        $routeParams = ['sortBy' => strtolower($this->translator->trans('sort.'.EntryCommentRepository::SORT_DEFAULT))];

        if ($time = $this->getCurrentRequest()->get('time')) {
            $routeParams['time'] = $time;
        }

        if ($this->isMagazinePage()) {
            $magazine = $this->getCurrentRequest()->get('magazine');

            $routeName           = 'magazine_entry_comments';
            $routeParams['name'] = $magazine->name;
        }

        if ($this->isSubPage()) {
            $routeName = 'entry_comments_subscribed';
        }

        if ($this->isModPage()) {
            $routeName = 'entry_comments_moderated';
        }

        if ($this->isTagPage()) {
            $routeName           = 'tag_entry_comments_front';
            $routeParams['name'] = $this->getCurrentRequest()->get('name');
        }

        if ($this->isDomainPage()) {
            $routeName           = 'domain_entry_comments_front';
            $routeParams['name'] = $this->getCurrentRequest()->get('name');
        }

        return $this->urlGenerator->generate(
            $routeName,
            $routeParams
        );
    }

    public function getActivePostsPagePath(): string
    {
        $routeName   = 'posts_front';
        $routeParams = ['sortBy' => strtolower($this->translator->trans('sort.'.PostRepository::SORT_DEFAULT))];

        if ($time = $this->getCurrentRequest()->get('time')) {
            $routeParams['time'] = $time;
        }

        if ($this->isMagazinePage()) {
            $magazine = $this->getCurrentRequest()->get('magazine');

            $routeName           = 'magazine_posts';
            $routeParams['name'] = $magazine->name;
        }

        if ($this->isSubPage()) {
            $routeName = 'posts_subscribed';
        }

        if ($this->isModPage()) {
            $routeName = 'posts_moderated';
        }

        if ($this->isTagPage()) {
            $routeName           = 'tag_posts_front';
            $routeParams['name'] = $this->getCurrentRequest()->get('name');
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
        return ($this->getCurrentRequest()->get('sortBy') ?? strtolower($this->translator->trans('sort.'.PostRepository::SORT_DEFAULT)))
            === $sortOption;
    }
}

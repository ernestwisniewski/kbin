<?php declare(strict_types=1);

namespace App\Twig;

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
            new TwigFunction('is_current_magazine_page', [$this, 'isCurrentMagazinePage']),
            new TwigFunction('is_active_sort_option', [$this, 'isActiveSortOption']),
            new TwigFunction('get_active_sort_option_path', [$this, 'getActiveSortOptionPath']),
            new TwigFunction('is_comments_page', [$this, 'isCommentsPage']),
            new TwigFunction('get_active_comments_page_path', [$this, 'getActiveCommentsPagePath']),
            new TwigFunction('is_active_comment_filter', [$this, 'isActiveCommentFilter']),
            new TwigFunction('get_active_comment_filter_path', [$this, 'getActiveCommentFilterPath']),
        ];
    }

    public function isHomePage(): bool
    {
        return in_array($this->getCurrentRouteName(), ['front', 'entry_comments']);
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
        if (!$this->getCurrentRequest()->get('magazine')) {
            return false;
        }

        return true;
    }

    public function isEntryPage(): bool
    {
        if (!$this->getCurrentRequest()->get('entry')) {
            return false;
        }

        return true;
    }

    public function isCommentsPage(): bool
    {
        return str_contains($this->getCurrentRouteName(), 'comments');
    }

    public function isActiveSortOption($sortOption): bool
    {
        if ($this->isCommentsPage()) {
            return false;
        }

        if ($this->isEntryPage()) {
            return false;
        }

        return ($requestSort = $this->getCurrentRequest()->get('sortBy') ?? EntryRepository::SORT_DEFAULT) === $sortOption;
    }

    public function getActiveSortOptionPath(string $sortOption): string
    {
        $routeName   = 'front';
        $routeParams = ['sortBy' => $sortOption ?? EntryRepository::SORT_DEFAULT];

        if ($this->isMagazinePage()) {
            $magazine            = $this->getCurrentRequest()->get('magazine');
            $routeName           = 'magazine';
            $routeParams['name'] = $magazine->getName();
        }

        if ($this->isSubPage()) {
            $routeName = 'subscribed';
        }

        return $this->urlGenerator->generate(
            $routeName,
            $routeParams
        );
    }

    public function getActiveCommentsPagePath()
    {
        $routeName   = 'entry_comments';
        $routeParams = ['sortBy' => EntryCommentRepository::SORT_DEFAULT];

        if ($this->isMagazinePage()) {
            $magazine = $this->getCurrentRequest()->get('magazine');

            $routeName           = 'magazine_comments';
            $routeParams['name'] = $magazine->getName();
        }

        if ($this->isSubPage()) {
            $routeName = 'subscribed_comments';
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

        $routeName = str_replace('entry_comment_edit', 'entry_comments', $this->getCurrentRouteName());

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

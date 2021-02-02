<?php declare(strict_types = 1);

namespace App\Twig;

use App\Entity\Magazine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PageContextExtension extends AbstractExtension
{
    private RequestStack $requestStack;
    private ?string $routeName = null;
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
            new TwigFunction('is_current_magazine_page', [$this, 'isCurrentMagazinePage']),
            new TwigFunction('is_entry_comments_page', [$this, 'isEntryCommentsPage']),
            new TwigFunction('is_active_sort_option', [$this, 'isActiveSortOption']),
            new TwigFunction('get_active_sort_option_path', [$this, 'getActiveSortOptionPath']),
            new TwigFunction('get_active_entry_comments_page_path', [$this, 'getActiveEntryCommentsPagePath']),
        ];
    }

    public function isHomePage(): bool
    {
        return in_array($this->getRouteName(), ['front', 'entry_comments']);
    }

    public function isSubPage(): bool
    {
        return str_contains($this->getRouteName(), 'subscribed');
    }

    public function isCurrentMagazinePage(Magazine $magazine): bool
    {
        if (!$magazineRequest = $this->requestStack->getCurrentRequest()->get('magazine')) {
            return false;
        }

        return $magazineRequest === $magazine;
    }

    public function isMagazinePage(): bool
    {
        if (!$magazineRequest = $this->requestStack->getCurrentRequest()->get('magazine')) {
            return false;
        }

        return true;
    }

    public function isEntryCommentsPage(): bool
    {
        return str_contains($this->getRouteName(), 'comments');
    }

    public function isActiveSortOption($sortOption): bool
    {
        if ($this->isEntryCommentsPage()) {
            return false;
        }

        return $this->requestStack->getCurrentRequest()->get('sortBy') === $sortOption;
    }

    public function getActiveSortOptionPath($sortOption): string
    {
        $routeName   = 'front';
        $routeParams = ['sortBy' => $sortOption];

        if ($this->isMagazinePage()) {
            $magazine            = $this->requestStack->getCurrentRequest()->get('magazine');
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

    public function getActiveEntryCommentsPagePath()
    {
        $routeName   = 'entry_comments';
        $routeParams = ['sortBy' => 'najnowsze'];

        if ($this->isMagazinePage()) {
            $magazine = $this->requestStack->getCurrentRequest()->get('magazine');

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
    
    private function getRouteName() {
        return $this->requestStack->getCurrentRequest()->get('_route');
    }
}

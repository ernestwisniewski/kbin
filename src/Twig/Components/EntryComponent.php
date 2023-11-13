<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('entry', template: 'components/_cached.html.twig')]
final class EntryComponent
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly CacheInterface $cache,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly Security $security
    ) {
    }

    public ?Entry $entry;
    public bool $isSingle = false;
    public bool $showShortSentence = true;
    public bool $showBody = false;
    public bool $showMagazineName = true;
    public bool $canSeeTrash = false;

    #[PostMount]
    public function postMount(array $attr): array
    {
        $this->canSeeTrashed();

        if ($this->isSingle) {
            $this->showMagazineName = false;

            if (isset($attr['class'])) {
                $attr['class'] = trim('entry--single section--top '.$attr['class']);
            } else {
                $attr['class'] = 'entry--single section--top';
            }
        }

        return $attr;
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $key = $this->isSingle.'_'.$this->showShortSentence.'_'.$this->showBody.'_'.$this->showMagazineName.'_';
        $key .= $this->canSeeTrash.$this->entry->getId().'_'.$this->security->getUser()?->getId().'_';
        $key .= $this->canSeeTrashed().'_'.$this->entry->cross.'_';
        $key .= $this->requestStack->getCurrentRequest()?->getLocale().'_';
        $key .= $this->requestStack->getCurrentRequest()->cookies->get(ThemeSettingsController::KBIN_ENTRIES_SHOW_THUMBNAILS).'_';
        $key .= $this->requestStack->getCurrentRequest()->cookies->get(ThemeSettingsController::KBIN_ENTRIES_SHOW_PREVIEW).'_';
        $key .= $this->requestStack->getCurrentRequest()->cookies->get(ThemeSettingsController::KBIN_ENTRIES_SHOW_USERS_AVATARS).'_';
        $key .= $this->requestStack->getCurrentRequest()->cookies->get(ThemeSettingsController::KBIN_ENTRIES_SHOW_MAGAZINES_ICONS).'_';
        $key .= $this->requestStack->getCurrentRequest()->cookies->get(ThemeSettingsController::KBIN_ENTRIES_SHOW_THUMBNAILS).'_';

        return $this->cache->get(
            "entries_cross_".hash('sha256', $key),
            function (ItemInterface $item) use ($attributes) {
                $item->expiresAfter(1800);

                $item->tag('entry_'.$this->entry->getId());

                return $this->twig->render(
                    'components/entry.html.twig',
                    [
                        'attributes' => $attributes,
                        'entry' => $this->entry,
                        'isSingle' => $this->isSingle,
                        'showShortSentence' => $this->showShortSentence,
                        'showBody' => $this->showBody,
                        'showMagazineName' => $this->showMagazineName,
                        'canSeeTrashed' => $this->canSeeTrashed(),
                    ]
                );
            }
        );
    }

    public function canSeeTrashed(): bool
    {
        if (VisibilityInterface::VISIBILITY_VISIBLE === $this->entry->visibility) {
            return true;
        }

        if (VisibilityInterface::VISIBILITY_TRASHED === $this->entry->visibility
            && $this->authorizationChecker->isGranted(
                'moderate',
                $this->entry
            )
            && $this->canSeeTrash) {
            return true;
        }

        $this->showBody = false;
        $this->showShortSentence = false;
        $this->entry->image = null;

        return false;
    }
}

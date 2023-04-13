<?php

namespace App\Twig\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('entry')]
final class EntryComponent
{
    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
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

    public function canSeeTrashed(): bool
    {
        if ($this->entry->visibility === VisibilityInterface::VISIBILITY_VISIBLE) {
            return true;
        }

        if ($this->entry->visibility === VisibilityInterface::VISIBILITY_TRASHED
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

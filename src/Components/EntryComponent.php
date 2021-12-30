<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry')]
class EntryComponent
{
    public Entry $entry;
    public string $titleTag = 'h4';
    public ?string $extraClass = null;
    public bool $showContent = false;
    public bool $directUrl = false;
    public bool $showMagazine = false;
    public bool $canSeeTrash = false;

    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function canSeeTrashed(): bool
    {
        if ($this->entry->visibility === VisibilityInterface::VISIBILITY_VISIBLE) {
            return true;
        }

        if ($this->entry->visibility === VisibilityInterface::VISIBILITY_TRASHED
            && $this->authorizationChecker->isGranted(
                'moderate',
                $this->entry->magazine
            )
            && $this->canSeeTrash) {
            return true;
        }

        return false;
    }
}

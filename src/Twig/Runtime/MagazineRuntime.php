<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use Symfony\Component\Security\Core\Security;
use Twig\Extension\RuntimeExtensionInterface;
use App\Entity\Magazine;

class MagazineRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function isSubscribed(Magazine $magazine): bool
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $magazine->isSubscribed($this->security->getUser());
    }

    public function isMagazineBlocked(Magazine $magazine): bool
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $this->security->getUser()->isBlockedMagazine($magazine);
    }
}

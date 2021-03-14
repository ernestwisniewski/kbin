<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\Magazine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\RuntimeExtensionInterface;

class MagazineRuntime implements RuntimeExtensionInterface
{
    private RequestStack $requestStack;
    private Security $security;

    public function __construct(
        Security $security,
        RequestStack $requestStack
    ) {
        $this->requestStack = $requestStack;
        $this->security     = $security;
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

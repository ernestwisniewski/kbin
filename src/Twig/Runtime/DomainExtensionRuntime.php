<?php

namespace App\Twig\Runtime;

use App\Entity\Domain;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\RuntimeExtensionInterface;

class DomainExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function isSubscribed(Domain $domain): bool
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $domain->isSubscribed($this->security->getUser());
    }

    public function isBlocked(Domain $domain): bool
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $this->security->getUser()->isBlockedDomain($domain);
    }
}

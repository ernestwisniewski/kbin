<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use App\Repository\EntryRepository;
use App\Twig\AppRuntime;
use App\Entity\Magazine;
use Twig\TwigFunction;

final class MagazineExtension extends AbstractExtension
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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_subscribed', [$this, 'isSubscribed']),
            new TwigFunction('is_magazine_blocked', [$this, 'isMagazineBlocked']),
        ];
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

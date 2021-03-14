<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\User;
use App\Twig\Runtime\UserRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use App\Repository\EntryRepository;
use App\Entity\Magazine;
use Twig\TwigFunction;

final class UserExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_user_follow', [UserRuntime::class, 'isUserFollow']),
            new TwigFunction('is_user_blocked', [UserRuntime::class, 'isUserBlocked']),
        ];
    }
}

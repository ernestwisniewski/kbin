<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\NavbarExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class NavbarExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('navbar_threads_url', [NavbarExtensionRuntime::class, 'navbarThreadsUrl']),
            new TwigFunction('navbar_posts_url', [NavbarExtensionRuntime::class, 'navbarPostsUrl']),
        ];
    }
}

<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class UserExtensionRuntime implements RuntimeExtensionInterface
{
    public function username($value): string
    {
        return ltrim($value, '@');
    }
}

<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Domain;

readonly class DomainShouldRatio
{
    public static function check(string $domain): bool
    {
        $domainsWithRatio = ['youtube.com', 'streamable.com', 'youtu.be', 'm.youtube.com'];

        return (bool) array_filter($domainsWithRatio, fn ($item) => str_contains($domain, $item));
    }
}

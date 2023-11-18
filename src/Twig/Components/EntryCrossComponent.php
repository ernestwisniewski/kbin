<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Entry;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_cross', template: 'components/entry_cross.html.twig')]
final class EntryCrossComponent
{
    public ?Entry $entry;
}

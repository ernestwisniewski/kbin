<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Entity\Entry;

trait EntryTemplateTrait
{
    private function getTemplateName(?string $type, ?bool $edit = false): string
    {
        $prefix = $edit ? 'edit' : 'create';

        if (!$type || Entry::ENTRY_TYPE_ARTICLE === $type) {
            return "entry/{$prefix}_article.html.twig";
        }

        if (Entry::ENTRY_TYPE_IMAGE === $type) {
            return "entry/{$prefix}_image.html.twig";
        }

        return "entry/{$prefix}_link.html.twig";
    }
}

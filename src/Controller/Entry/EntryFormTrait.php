<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Entity\Entry;
use App\Kbin\Entry\DTO\EntryDto;
use App\Kbin\Entry\Form\EntryArticleType;
use App\Kbin\Entry\Form\EntryImageType;
use App\Kbin\Entry\Form\EntryLinkType;
use Symfony\Component\Form\FormInterface;

/**
 * @method createForm(string $class)
 */
trait EntryFormTrait
{
    private function createFormByType(string $type, EntryDto $dto = null): FormInterface
    {
        if (Entry::ENTRY_TYPE_ARTICLE === $type) {
            return $this->createForm(EntryArticleType::class, $dto);
        }

        if (Entry::ENTRY_TYPE_IMAGE === $type) {
            return $this->createForm(EntryImageType::class, $dto);
        }

        return $this->createForm(EntryLinkType::class, $dto);
    }
}

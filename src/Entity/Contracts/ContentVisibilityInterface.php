<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Contracts;

interface ContentVisibilityInterface extends ContentInterface
{
    public function getVisibility(): string;

    public function isVisible(): bool;

    public function isTrashed(): bool;

    public function isPrivate(): bool;

    public function isSoftDeleted(): bool;
}

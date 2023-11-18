<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Contracts;

interface VisibilityInterface
{
    public const VISIBILITY_VISIBLE = 'visible';
    public const VISIBILITY_SOFT_DELETED = 'soft_deleted';
    public const VISIBILITY_TRASHED = 'trashed';
    public const VISIBILITY_PRIVATE = 'private';

    public function getVisibility(): string;

    public function isVisible(): bool;

    public function isTrashed(): bool;

    public function isPrivate(): bool;

    public function isSoftDeleted(): bool;

    public function softDelete(): void;

    public function trash(): void;

    public function restore(): void;
}

<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown\CommonMark\Node;

use App\Markdown\CommonMark\MentionType;

interface MentionLink
{
    public function getKbinUsername(): string;

    public function getTitle(): ?string;

    public function getType(): MentionType;
}

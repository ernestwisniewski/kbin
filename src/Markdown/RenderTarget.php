<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown;

enum RenderTarget: string
{
    case Page = 'Page';
    case ActivityPub = 'ActivityPub';
}

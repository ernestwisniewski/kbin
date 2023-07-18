<?php

declare(strict_types=1);

namespace App\Markdown;

enum RenderTarget: string {
    case Page        = "Page";
    case ActivityPub = "ActivityPub";
}
<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark\Node;

use App\Markdown\CommonMark\MentionType;

interface MentionLink
{
    public function getKbinUsername(): string;
    public function getTitle(): ?string;
    public function getType(): MentionType;
}
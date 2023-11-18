<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Markdown\CommonMark\Node;

use App\Markdown\CommonMark\MentionType;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

class ActivityPubMentionLink extends Link implements MentionLink
{
    public function __construct(
        string $activityPubUrl,
        string $label,
        string $title,
        private string $kbinUsername,
        private MentionType $type,
    ) {
        parent::__construct($activityPubUrl, $label, $title);
    }

    public function getKbinUsername(): string
    {
        return $this->kbinUsername;
    }

    public function getType(): MentionType
    {
        return $this->type;
    }
}

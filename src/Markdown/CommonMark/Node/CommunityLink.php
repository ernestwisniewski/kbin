<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark\Node;

use App\Markdown\CommonMark\MentionType;

class CommunityLink extends ActivityPubMentionLink
{
    public function __construct(
        string $url,
        string $label,
        string $title,
        private string $kbinUsername,
        private MentionType $type,
    ) {
        parent::__construct($url, $label, $title, $kbinUsername, $type);
    }
}

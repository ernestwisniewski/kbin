<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark\Node;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

class MentionLink extends Link
{
    public function __construct(
        string $url, 
        ?string $label = null, 
        ?string $title = null,
        ?string $kbinUsername = null,
    ) {
        parent::__construct($url, $label, $title);

        $this->data->set('title', $this->getTitle());
        $this->data->set('kbinUsername', $kbinUsername);
    }
}
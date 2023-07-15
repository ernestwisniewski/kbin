<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark\Node;

use App\Markdown\CommonMark\MentionType;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

class MentionLink extends Link
{
    public function __construct(
        string $url,
        string $label,
        string $title,
        string $kbinUsername,
        MentionType $type,
    ) {
        parent::__construct($url, $label, $title);

        $this->data->set('title', $this->getTitle());
        $this->data->set('kbinUsername', $kbinUsername);
        $this->data->set('type', $type);
    }

    public function getKbinUsername(): string
    {
        return $this->data->get('kbinUsername');
    }

    public function getType(): MentionType
    {
        return $this->data->get('type');
    }
}

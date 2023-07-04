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
        private string $route,
        private string $kbinUsername,
        private MentionType $type,
    ) {
        parent::__construct($url, $label, $title);
    }

    public function getKbinUsername(): string
    {
        return $this->kbinUsername;
    }
    
    public function getRoute(): string
    {
        return $this->route;
    }

    public function getType(): MentionType
    {
        return $this->type;
    }
}
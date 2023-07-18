<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark\Node;

use App\Markdown\CommonMark\MentionType;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

class RoutedMentionLink extends Link implements MentionLink
{
    public function __construct(
        private string $route,
        private string $paramName,
        private string $slug,
        string $label,
        string $title,
        private string $kbinUsername,
        private MentionType $type,
    ) {
        parent::__construct($slug, $label, $title);
    }

    public function getKbinUsername(): string
    {
        return $this->kbinUsername;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getParamName(): string
    {
        return $this->paramName;
    }

    public function getType(): MentionType
    {
        return $this->type;
    }
}

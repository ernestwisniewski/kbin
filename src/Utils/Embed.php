<?php declare(strict_types=1);

namespace App\Utils;

use Embed\Embed as BaseEmbed;

class Embed
{
    private ?string $title;
    private ?string $image;
    private ?string $embed;

    public function fetch($url): self
    {
        $embed  = (new BaseEmbed())->get($url);
        $oembed = $embed->getOEmbed();

        $this->title = $oembed->str('title');
        $this->image = $oembed->str('image');
        $this->embed = $this->cleanIframe($oembed->html('html'));
        if (!$this->embed && $embed->code) {
            $this->embed = $this->cleanIframe($embed->code->html);
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getEmbed(): ?string
    {
        return $this->embed;
    }

    private function cleanIframe(?string $html): ?string
    {
        return $html ? preg_replace('/(width|height)(=)"([\d]+)"/', '${1}${2}"100%"', $html) : null;
    }
}

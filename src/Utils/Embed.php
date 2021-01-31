<?php declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Embed\Embed as BaseEmbed;
use Symfony\Contracts\Cache\ItemInterface;

class Embed
{
    private ?string $title = null;
    private ?string $image = null;
    private ?string $embed = null;

    public function fetch($url): self
    {
        $cache = new FilesystemAdapter();

        return $cache->get(
            'embed_'.md5($url),
            function (ItemInterface $item) use ($url) {
                $item->expiresAfter(3600);

                try {
                    $embed  = (new BaseEmbed())->get($url);
                    $oembed = $embed->getOEmbed();
                } catch (\Exception $e) {
                    return $this;
                }

                $this->title = $embed->title;
                $this->image = (string) $embed->image;
                $this->embed = $this->cleanIframe($oembed->html('html'));

                if (!$this->embed && $embed->code) {
                    $this->embed = $this->cleanIframe($embed->code->html);
                }

                return $this;
            }
        );
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

<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Entry;
use App\Service\ImageManager;
use App\Service\SettingsManager;
use Embed\Embed as BaseEmbed;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Embed
{
    public ?string $url = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?string $image = null;
    public ?string $html = null;

    public function __construct(private CacheInterface $cache, private SettingsManager $settings)
    {
    }

    public function fetch($url): self
    {
        if ($this->settings->isLocalUrl($url)) {
            return $this;
        }

        return $this->cache->get(
            'embed_'.md5($url),
            function (ItemInterface $item) use ($url) {
                $item->expiresAfter(3600);

                try {
                    $embed = (new BaseEmbed())->get($url);
                    $oembed = $embed->getOEmbed();
                } catch (\Exception $e) {
                    $c = clone $this;
                    unset($c->cache);
                    unset($c->settings);

                    return $c;
                }

                $this->url = $url;
                $this->title = $embed->title;
                $this->description = $embed->description;
                $this->image = (string) $embed->image;
                $this->html = $this->cleanIframe($oembed->html('html'));

                try {
                    if (!$this->html && $embed->code) {
                        $this->html = $this->cleanIframe($embed->code->html);
                    }
                } catch (\TypeError $e) {
                }

                $c = clone $this;
                unset($c->cache);
                unset($c->settings);

                return $c;
            }
        );
    }

    private function cleanIframe(?string $html): ?string
    {
        if (!$html || str_contains($html, 'wp-embedded-content')) {
            return null;
        }

        return $html;

        //        $types = [
        //            str_starts_with($html, '<iframe'),
        //            str_starts_with($html, '<video'),
        //            str_starts_with($html, '<img'),
        //        ];
        //
        //        if (count(array_unique($types)) === 1) {
        //            return null;
        //        }
        //
        //        return preg_replace('/(height)(=)"([\d]+)"/', '${1}${2}"auto"', $html);
    }

    public function getType(): string
    {
        if ($this->isImageUrl()) {
            return Entry::ENTRY_TYPE_IMAGE;
        }

        if ($this->isVideoUrl()) {
            return Entry::ENTRY_TYPE_IMAGE;
        }

        if ($this->isVideoEmbed()) {
            return Entry::ENTRY_TYPE_VIDEO;
        }

        return Entry::ENTRY_TYPE_LINK;
    }

    public function isImageUrl(): bool
    {
        if (!$this->url) {
            return false;
        }

        return ImageManager::isImageUrl($this->url);
    }

    private function isVideoUrl(): bool
    {
        return false;
    }

    private function isVideoEmbed(): bool
    {
        if (!$this->html) {
            return false;
        }

        return str_contains($this->html, 'video')
            || str_contains($this->html, 'youtube')
            || str_contains($this->html, 'vimeo')
            || str_contains($this->html, 'streamable'); // @todo
    }
}

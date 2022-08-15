<?php declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Image;
use App\Service\ImageManager;
use App\Service\SettingsManager;

class ImageWrapper
{
    public function __construct(
        private SettingsManager $settings,
        private ImageManager $imageManager
    ) {
    }

    public function build(array $item, Image $image, string $title = ''): array
    {
        $item['attachment'][] = [
            'type'       => 'Document',
            'mediaType'  => $this->imageManager->getMimetype($image),
            'url'        => 'https://'.$this->settings->get('KBIN_DOMAIN').'/media/'.$image->filePath, // @todo media url
            'name'       => $title, // @todo media url
//            'blurhash'   => '', // @todo mastodon blurhash
            'focalPoint' => [0, 0],
            'width'      => $image->width,
            'height'     => $image->height,
        ];

        $item['image'] = [ // @todo Lemmy
            'type' => 'Image',
            'url'  => 'https://'.$this->settings->get('KBIN_DOMAIN').'/media/'.$image->filePath // @todo media url
        ];

        return $item;
    }
}

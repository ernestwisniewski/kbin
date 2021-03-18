<?php declare(strict_types=1);

namespace App\Form\Constraint;

use App\Service\ImageManager;
use Symfony\Component\Validator\Constraints\Image;

class ImageConstraint
{
    public static function default(): Image
    {
        return new Image(
            [
                'detectCorrupted' => true,
                'groups'          => ['upload'],
                'maxSize'         => '12M',
                'mimeTypes'       => ImageManager::IMAGE_MIMETYPES,
            ]
        );
    }
}

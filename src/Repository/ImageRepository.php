<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Image;
use App\Service\ImageManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use kornrunner\Blurhash\Blurhash;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image|null findOneBySha256($sha256)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    private ImageManager $imageManager;

    public function __construct(ManagerRegistry $registry, ImageManager $imageManager)
    {
        parent::__construct($registry, Image::class);
        $this->imageManager = $imageManager;
    }

    public function findOrCreateFromUpload($upload): ?Image
    {
        return $this->findOrCreateFromPath($upload->getPathname());
    }

    public function findOrCreateFromPath(string $source): ?Image
    {
        $fileName = $this->imageManager->getFileName($source);
        $filePath = $this->imageManager->getFilePath($source);
        $sha256   = hash_file('sha256', $source, true);

        if ($image = $this->findOneBySha256($sha256)) {
            return $image;
        }

        [$width, $height] = @getimagesize($source);
        $blurhash = $this->blurhash($source);

        $image = new Image($fileName, $filePath, $sha256, $width, $height, $blurhash);

        if (!$image->width || !$image->height) {
            [$width, $height] = @getimagesize($source);
            $image->setDimensions($width, $height);
        }

        try {
            $this->imageManager->store($source, $filePath);
        } catch (Exception $e) {
            return null;
        }

        return $image;
    }

    public function blurhash(string $filePath): string
    {
        $image = imagecreatefromstring(file_get_contents($filePath));
        $width = imagesx($image);
        $height = imagesy($image);

        $pixels = [];
        for ($y = 0; $y < $height; ++$y) {
            $row = [];
            for ($x = 0; $x < $width; ++$x) {
                $index = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $index);

                $row[] = [$colors['red'], $colors['green'], $colors['blue']];
            }
            $pixels[] = $row;
        }

        $components_x = 4;
        $components_y = 3;

        return Blurhash::encode($pixels, $components_x, $components_y);
    }
}

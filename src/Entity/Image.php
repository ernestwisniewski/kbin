<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: ImageRepository::class)]
#[Table]
#[UniqueConstraint(name: 'images_file_name_idx', columns: ['file_name'])]
#[UniqueConstraint(name: 'images_sha256_idx', columns: ['sha256'])]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
class Image
{
    #[Column(type: 'string')]
    public string $filePath;
    #[Column(type: 'string')]
    public string $fileName;
    #[Column(type: 'binary', length: 32)]
    public $sha256;
    #[Column(type: 'integer', nullable: true)]
    public ?int $width = null;
    #[Column(type: 'integer', nullable: true)]
    public ?int $height;
    #[Column(type: 'string', nullable: true)]
    public ?string $blurhash = null;
    #[Column(type: 'text', nullable: true)]
    public ?string $altText = null;
    #[Column(type: 'string', nullable: true)]
    public ?string $sourceUrl = null;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(
        string $fileName,
        string $filePath,
        string $sha256,
        ?int $width,
        ?int $height,
        ?string $blurhash
    ) {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->blurhash = $blurhash;

        error_clear_last();
        if (64 === \strlen($sha256)) {
            $sha256 = @hex2bin($sha256);

            if (false === $sha256) {
                throw new \InvalidArgumentException(error_get_last()['message']);
            }
        } elseif (32 !== \strlen($sha256)) {
            throw new \InvalidArgumentException('$sha256 must be a SHA256 hash in raw or binary form');
        }

        $this->sha256 = $sha256;
        $this->setDimensions($width, $height);
    }

    public function setDimensions(?int $width, ?int $height): void
    {
        if (null !== $width && $width <= 0) {
            throw new \InvalidArgumentException('$width must be NULL or >0');
        }

        if (null !== $height && $height <= 0) {
            throw new \InvalidArgumentException('$height must be NULL or >0');
        }

        if (($width && $height) || (!$width && !$height)) {
            $this->width = $width;
            $this->height = $height;
        } else {
            throw new \InvalidArgumentException('$width and $height must both be set or NULL');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->fileName;
    }

    //    public function getSha256(): string
    //    {
    //        return bin2hex($this->sha256);
    //    }

    public function __sleep()
    {
        return [];
    }
}

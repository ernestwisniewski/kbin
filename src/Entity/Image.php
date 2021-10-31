<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use function strlen;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="images_file_name_idx", columns={"file_name"}),
 *     @ORM\UniqueConstraint(name="images_sha256_idx", columns={"sha256"}),
 * })
 */
class Image
{
    /**
     * @ORM\Column(type="string")
     */
    public string $filePath;
    /**
     * @ORM\Column(type="string")
     */
    public string $fileName;
    /**
     * @ORM\Column(type="binary", length=32)
     */
    public $sha256;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public ?int $width;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public ?int $height;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(string $fileName, string $filePath, string $sha256, ?int $width, ?int $height)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;

        error_clear_last();
        if (strlen($sha256) === 64) {
            $sha256 = @hex2bin($sha256);

            if ($sha256 === false) {
                throw new InvalidArgumentException(error_get_last()['message']);
            }
        } elseif (strlen($sha256) !== 32) {
            throw new InvalidArgumentException(
                '$sha256 must be a SHA256 hash in raw or binary form'
            );
        }

        $this->sha256 = $sha256;
        $this->setDimensions($width, $height);
    }

    public function setDimensions(?int $width, ?int $height): void
    {
        if ($width !== null && $width <= 0) {
            throw new InvalidArgumentException('$width must be NULL or >0');
        }

        if ($height !== null && $height <= 0) {
            throw new InvalidArgumentException('$height must be NULL or >0');
        }

        if (($width && $height) || (!$width && !$height)) {
            $this->width  = $width;
            $this->height = $height;
        } else {
            throw new InvalidArgumentException('$width and $height must both be set or NULL');
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

    public function getSha256(): string
    {
        return bin2hex($this->sha256);
    }

    public function __sleep()
    {
        return [];
    }
}

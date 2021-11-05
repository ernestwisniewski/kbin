<?php declare(strict_types = 1);

namespace App\DTO;

class ImageDto
{
    public ?string $filePath = null;
    public ?int $width = null;
    public ?int $height = null;

    public function create(string $filePath, ?int $width = null, ?int $height = null, ?int $id = null): self
    {
        $this->id       = $id;
        $this->filePath = $filePath;
        $this->width    = $width;
        $this->height   = $height;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

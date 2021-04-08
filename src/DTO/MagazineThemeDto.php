<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Magazine;

class MagazineThemeDto
{
    private Magazine $magazine;

    private ?Image $cover = null;

    private ?string $customCss = null;

    private ?string $customJs = null;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;
        $this->customCss = $magazine->getCustomCss();
    }

    public function create(?Image $cover)
    {
        $this->cover = $cover;
    }

    public function getMagazine(): Magazine
    {
        return $this->magazine;
    }

    public function getCover(): ?Image
    {
        return $this->cover;
    }

    public function setCover(?Image $cover): void
    {
        $this->cover = $cover;
    }

    public function getCustomCss(): ?string
    {
        return $this->customCss;
    }

    public function setCustomCss(?string $customCss): self
    {
        $this->customCss = $customCss;

        return $this;
    }

    public function getCustomJs(): ?string
    {
        return $this->customJs;
    }

    public function setCustomJs(?string $customJs): self
    {
        $this->customJs = $customJs;

        return $this;
    }
}

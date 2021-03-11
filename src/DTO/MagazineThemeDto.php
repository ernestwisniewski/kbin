<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Magazine;

class MagazineThemeDto
{
    private Magazine $magazine;
    private ?Image $cover = null;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;
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
}

<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\EntryCommentReport;
use App\Entity\EntryReport;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostCommentReport;
use App\Entity\PostReport;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class BadgeDto
{
    private ?int $id = null;

    private ?Magazine $magazine = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 1,
     *     max = 20
     * )
     */
    private ?string $name = null;

    public function create(Magazine $magazine, ?int $id = null): self
    {
        $this->id       = $id;
        $this->magazine = $magazine;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(?Magazine $magazine): BadgeDto
    {
        $this->magazine = $magazine;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): BadgeDto
    {
        $this->name = $name;

        return $this;
    }
}

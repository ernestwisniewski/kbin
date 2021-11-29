<?php declare(strict_types = 1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class PostDto
{
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    public Image|ImageDto|null $image = null;
    #[Assert\Length(min: 2, max: 5000)]
    public ?string $body = null;
    public bool $isAdult = false;
    public ?string $slug = null;
    public int $comments = 0;
    public int $uv = 0;
    public int $dv = 0;
    public int $score = 0;
    public ?string $visibility = null;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTime $lastActive = null;
    public ?string $ip = null;
    public ?Collection $bestComments = null;
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}

<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\EmbedRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="url_idx", columns={"url"}),
 * })
 * @ORM\Entity(repositoryClass=EmbedRepository::class)
 */
class Embed
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    public function __construct(string $url, bool $embed)
    {
        $this->url      = $url;
        $this->hasEmbed = $embed;

        $this->createdAtTraitConstruct();
    }

    /**
     * @ORM\Column(type="string")
     */
    public string $url;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $hasEmbed = false;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}

<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="magazine_block_idx",
 *         columns={"user_id", "magazine_id"}
 *     )
 * })
 * @ORM\Entity()
 */
class MagazineBlock
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="blockedMagazines")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?User $user;
    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?Magazine $magazine;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    public function __construct(User $user, Magazine $magazine)
    {
        $this->createdAtTraitConstruct();

        $this->user     = $user;
        $this->magazine = $magazine;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __sleep()
    {
        return [];
    }
}

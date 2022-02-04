<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="domain_block_idx",
 *         columns={"user_id", "domain_id"}
 *     )
 * })
 * @ORM\Entity()
 */
class DomainBlock
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="blockedDomains")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?User $user;
    /**
     * @ORM\ManyToOne(targetEntity=Domain::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?Domain $domain;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    public function __construct(User $user, Domain $domain)
    {
        $this->createdAtTraitConstruct();

        $this->user   = $user;
        $this->domain = $domain;
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

<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\AwardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AwardRepository::class)
 */
class Award
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="awards")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    public User $user;
    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="awards")
     * @ORM\JoinColumn(nullable=true, onDelete="cascade")
     */
    public ?Magazine $magazine;
    /**
     * @ORM\ManyToOne(targetEntity=AwardType::class, inversedBy="awards")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    public AwardType $type;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;
}

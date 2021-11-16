<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="App\Repository\CardanoPaymentInitRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="cpi_type", type="text")
 * @ORM\DiscriminatorMap({
 *   "entry": "EntryCardanoPaymentInit",
 * })
 */
abstract class CardanoPaymentInit
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class)
     * @ORM\JoinColumn(onDelete="cascade")
     */
    public Magazine $magazine;
    /**
     * @ORM\JoinColumn(nullable=true)
     * @ORM\ManyToOne(targetEntity="User")
     */
    public ?User $user = null;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(Magazine $magazine, ?User $user = null)
    {
        $this->user     = $user;
        $this->magazine = $magazine;

        $this->createdAtTraitConstruct();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    abstract public function getType(): string;

    abstract public function getSubject(): ContentInterface;

    abstract public function clearSubject(): self;
}

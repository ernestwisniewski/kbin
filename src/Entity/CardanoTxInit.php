<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="App\Repository\CardanoTxInitRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="cpi_type", type="text")
 * @ORM\DiscriminatorMap({
 *   "entry": "EntryCardanoTxInit",
 * })
 */
abstract class CardanoTxInit
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
     * @ORM\Column(type="string")
     */
    public string $sessionId;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(Magazine $magazine, string $sessionId, ?User $user = null)
    {
        $this->user      = $user;
        $this->magazine  = $magazine;
        $this->sessionId = $sessionId;

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

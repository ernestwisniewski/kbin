<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="App\Repository\CardanoTxRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="ctx_type", type="text")
 * @ORM\DiscriminatorMap({
 *   "entry": "EntryCardanoTx",
 * })
 */
abstract class CardanoTx
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
     * @ORM\JoinColumn()
     * @ORM\ManyToOne(targetEntity="User")
     */
    public User $receiver;
    /**
     * @ORM\JoinColumn(nullable=true)
     * @ORM\ManyToOne(targetEntity="User")
     */
    public ?User $sender = null;
    /**
     * @ORM\Column(type="integer")
     */
    public int $amount;
    /**
     * @ORM\Column(type="string")
     */
    public string $txHash;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(
        Magazine $magazine,
        int $amount,
        string $txHash,
        \DateTimeImmutable $createdAt,
        ?User $sender = null,
    ) {
        $this->magazine  = $magazine;
        $this->sender    = $sender;
        $this->amount    = $amount;
        $this->txHash    = $txHash;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    abstract public function getType(): string;

    abstract public function getSubject(): ContentInterface;

    abstract public function clearSubject(): self;
}

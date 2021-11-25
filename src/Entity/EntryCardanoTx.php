<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntryCardanoTx extends CardanoTx
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="cardanoTx")
     */
    public ?Entry $entry;

    public function __construct(
        ContentInterface $entry,
        int $amount,
        string $txHash,
        \DateTimeImmutable $createdAt,
        ?User $sender = null,
    ) {

        parent::__construct($entry->magazine, $amount, $txHash, $createdAt, $sender);
        $this->entry    = $entry;
        $this->receiver = $entry->user;
    }

    public function getSubject(): Entry
    {
        return $this->entry;
    }

    public function clearSubject(): EntryCardanoTx
    {
        $this->entry = null;

        return $this;
    }

    public function getType(): string
    {
        return 'entry';
    }
}

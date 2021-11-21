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
     * @ORM\ManyToOne(targetEntity="Entry")
     */
    public ?Entry $entry;

    public function __construct(
        ContentInterface $entry,
        User $receiver,
        int $amount,
        string $txHash,
        \DateTimeImmutable $createdAt,
        ?User $sender = null,
    ) {

        parent::__construct($entry->magazine, $receiver, $amount, $txHash, $createdAt, $sender);
        $this->entry = $entry;
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

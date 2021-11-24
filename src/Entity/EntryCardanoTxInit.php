<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntryCardanoTxInit extends CardanoTxInit
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="cardanoTx")
     */
    public ?Entry $entry;

    public function __construct(ContentInterface $entry, string $sessionId, ?User $user = null)
    {
        parent::__construct($entry->magazine, $sessionId, $user);

        $this->entry = $entry;
    }

    public function getSubject(): Entry
    {
        return $this->entry;
    }

    public function clearSubject(): EntryCardanoTxInit
    {
        $this->entry = null;

        return $this;
    }

    public function getType(): string
    {
        return 'entry';
    }
}

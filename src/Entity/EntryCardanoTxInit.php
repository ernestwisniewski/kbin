<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class EntryCardanoTxInit extends CardanoTxInit
{
    #[ManyToOne(targetEntity: Entry::class, inversedBy: 'cardanoTx')]
    #[JoinColumn(nullable: true)]
    public Entry|ContentInterface|null $entry = null;

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

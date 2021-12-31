<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MagazineLogEntryRestored extends MagazineLog
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Entry $entry;

    public function __construct(Entry $entry, User $user)
    {
        parent::__construct($entry->magazine, $user);

        $this->entry = $entry;
    }

    public function getType(): string
    {
        return 'log_entry_restored';
    }

    public function getSubject(): ContentInterface
    {
        return $this->entry;
    }

    public function clearSubject(): MagazineLog
    {
        $this->entry = null;

        return $this;
    }
}

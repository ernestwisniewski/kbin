<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Repository\MagazineLogEntryDeleteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MagazineLogEntryDeleteRepository")
 */
class MagazineLogEntryDelete extends MagazineLog
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Entry $entry;

    public function __construct(Entry $entry, User $user)
    {
        parent::__construct($entry->getMagazine(), $user);

        $this->entry = $entry;
    }

    public function getType(): string
    {
        return 'log_entry_delete';
    }

    public function getEntry(): Entry
    {
        return $this->entry;
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

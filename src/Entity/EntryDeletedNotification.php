<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntryDeletedNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="notifications")
     */
    public ?Entry $entry;

    public function __construct(User $receiver, Entry $entry)
    {
        parent::__construct($receiver);

        $this->entry = $entry;
    }

    public function getSubject(): Entry
    {
        return $this->entry;
    }

    public function getType(): string
    {
        return 'entry_deleted_notification';
    }
}

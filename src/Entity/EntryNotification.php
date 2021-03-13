<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\EntryNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryNotificationRepository::class)
 */
class EntryNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="notifications")
     */
    private ?Entry $entry;

    public function __construct(User $receiver, Entry $entry)
    {
        parent::__construct($receiver);

        $this->entry = $entry;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    public function getSubject(): Entry
    {
        return $this->entry;
    }

    public function getType(): string
    {
        return 'entry_notification';
    }
}

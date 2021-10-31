<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MagazineBanNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="MagazineBan")
     */
    public ?MagazineBan $ban;

    public function __construct(User $receiver, MagazineBan $ban)
    {
        parent::__construct($receiver);

        $this->ban = $ban;
    }

    public function getSubject(): MagazineBan
    {
        return $this->ban;
    }

    public function getType(): string
    {
        return 'magazine_ban_notification';
    }
}

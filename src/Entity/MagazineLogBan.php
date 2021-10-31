<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MagazineLogBan extends MagazineLog
{
    /**
     * @ORM\ManyToOne(targetEntity="MagazineBan")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?MagazineBan $ban;

    /**
     * @ORM\Column(type="string")
     */
    public string $meta = 'ban';

    public function __construct(MagazineBan $ban)
    {
        parent::__construct($ban->magazine, $ban->bannedBy);

        $this->ban = $ban;

        if ($ban->expiredAt < new DateTime('+10 seconds')) {
            $this->meta = 'unban';
        }
    }

    public function getType(): string
    {
        return 'log_ban';
    }

    public function getSubject(): ContentInterface|null
    {
        return null;
    }

    public function clearSubject(): MagazineLog
    {
        $this->ban = null;

        return $this;
    }
}

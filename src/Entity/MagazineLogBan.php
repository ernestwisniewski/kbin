<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Repository\MagazineLogEntryDeleteRepository;
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
    private ?MagazineBan $ban;

    /**
     * @ORM\Column(type="string")
     */
    private string $meta = 'ban';

    public function __construct(MagazineBan $ban)
    {
        parent::__construct($ban->getMagazine(), $ban->getBannedBy());

        $this->ban = $ban;

        if ($ban->getExpiredAt() < new \DateTime('+10 seconds')) {
            $this->meta = 'unban';
        }
    }

    public function getType(): string
    {
        return 'log_ban';
    }

    public function getBan(): MagazineBan
    {
        return $this->ban;
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

    public function getMeta(): string
    {
        return $this->meta;
    }
}

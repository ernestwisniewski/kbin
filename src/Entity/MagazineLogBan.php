<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class MagazineLogBan extends MagazineLog
{
    #[ManyToOne(targetEntity: MagazineBan::class)]
    #[JoinColumn(onDelete: 'SET NULL')]
    public ?MagazineBan $ban = null;

    #[Column(type: 'string')]
    public string $meta = 'ban';

    public function __construct(MagazineBan $ban)
    {
        parent::__construct($ban->magazine, $ban->bannedBy);

        $this->ban = $ban;

        if ($ban->expiredAt < new \DateTime('+10 seconds')) {
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

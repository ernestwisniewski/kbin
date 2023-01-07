<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Tchoulom\ViewCounterBundle\Entity\ViewCounter as BaseViewCounter;
use Tchoulom\ViewCounterBundle\Entity\ViewCounterInterface;
use Tchoulom\ViewCounterBundle\Model\ViewCountable;

#[Entity]
#[Table(name: 'view_counter')]
class ViewCounter extends BaseViewCounter
{
    #[ManyToOne(targetEntity: Entry::class, cascade: ['persist'], inversedBy: 'viewCounters')]
    #[JoinColumn]
    public ViewCountable $entry;

    public function getPage(): ?ViewCountable
    {
        return $this->entry;
    }

    public function setPage(ViewCountable $page): ViewCounter
    {
        $this->entry = $page;

        return $this;
    }

    public function getEntry(): ViewCountable
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry): ViewCounterInterface
    {
        $this->entry = $entry;

        return $this;
    }
}

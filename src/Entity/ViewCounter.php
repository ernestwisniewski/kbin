<?php declare(strict_types=1);

namespace App\Entity;

use Tchoulom\ViewCounterBundle\Entity\ViewCounter as BaseViewCounter;
use Doctrine\ORM\Mapping as ORM;
use Tchoulom\ViewCounterBundle\Model\ViewCountable;
use Tchoulom\ViewCounterBundle\Entity\ViewCounterInterface;

/**
 * @ORM\Table(name="view_counter")
 * @ORM\Entity()
 */
class ViewCounter extends BaseViewCounter
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Entry", cascade={"persist"}, inversedBy="viewCounters")
     * @ORM\JoinColumn(nullable=true)
     */
    private ViewCountable $entry;

    public function getPage(): ViewCountable
    {
        return $this->entry;
    }

    public function setPage(ViewCountable $page): ViewCounterInterface
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

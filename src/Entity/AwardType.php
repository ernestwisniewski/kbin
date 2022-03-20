<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\AwardTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AwardTypeRepository::class)
 */
class AwardType
{
    /**
     * @ORM\Column(type="string")
     */
    public string $name;
    /**
     * @ORM\Column(type="string")
     */
    public string $category;
    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    public int $count = 0;
    /**
     * @ORM\Column(type="array", nullable=true, options={"default" : null})
     */
    public array $attributes;
    /**
     * @ORM\OneToMany(targetEntity="Award", mappedBy="type", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id": "DESC"})
     */
    public Collection $awards;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct()
    {
        $this->awards = new ArrayCollection();
    }
}

<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Contracts\DomainInterface;
use App\Repository\DomainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="domain_name_idx", columns={"name"}),
 * })
 * @ORM\Entity(repositoryClass=DomainRepository::class)
 */
class Domain
{
    /**
     * @ORM\OneToMany(targetEntity=Entry::class, mappedBy="domain")
     */
    public Collection $entries;
    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $name;
    /**
     * @ORM\Column(type="integer")
     */
    public int $entryCount = 0;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(DomainInterface $entry, string $name)
    {
        $this->name    = $name;
        $this->entries = new ArrayCollection();

        $this->addEntry($entry);
    }

    public function addEntry(DomainInterface $subject): self
    {
        if (!$this->entries->contains($subject)) {
            $this->entries->add($subject);
            $subject->setDomain($this);
        }

        $this->updateCounts();

        return $this;
    }

    public function updateCounts()
    {
        $this->entryCount = $this->entries->count();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function removeEntry(DomainInterface $subject): self
    {
        if ($this->entries->removeElement($subject)) {
            if ($subject->getDomain() === $this) {
                $subject->setDomain(null);
            }
        }

        $this->updateCounts();

        return $this;
    }

    public function __sleep()
    {
        return [];
    }
}

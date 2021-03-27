<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Contracts\DomainInterface;
use App\Repository\DomainRepository;
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
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToMany(targetEntity=Entry::class, mappedBy="domain")
     */
    private Collection $entries;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer")
     */
    private int $entryCount = 0;

    public function __construct(DomainInterface $entry, string $name)
    {
        $this->name    = $name;
        $this->entries = new ArrayCollection();

        $this->addEntry($entry);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntries(): Collection
    {
        return $this->entries;
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

    public function updateCounts()
    {
        $this->entryCount = $this->entries->count();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEntryCount(): ?int
    {
        return $this->entryCount;
    }

    public function setEntryCount(int $entryCount): self
    {
        $this->entryCount = $entryCount;

        return $this;
    }

    public function __sleep()
    {
        return [];
    }
}

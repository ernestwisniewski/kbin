<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="report_type", type="text")
 * @ORM\DiscriminatorMap({
 *     "entry": "EntryReport",
 *     "entry_comment": "EntryCommentReport",
 *     "post": "PostReport",
 *     "post_comment": "PostCommentReport",
 * })
 */
abstract class Report
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPEAL = 'appeal';
    const STATUS_CLOSED = 'closed';

    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Magazine", inversedBy="reports")
     */
    private Magazine $magazine;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reports")
     */
    private User $reporting;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="violations")
     */
    private User $reported;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $reason = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $weight = 1;

    /**
     * @ORM\JoinColumn(nullable=true)
     * @ORM\ManyToOne(targetEntity="User")
     */
    private ?User $consideredBy = null;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private ?\DateTime $consideredAt = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $status = self::STATUS_PENDING;

    public function __construct(User $reporting, User $reported, Magazine $magazine, ?string $reason = null)
    {
        $this->reporting = $reporting;
        $this->reported  = $reported;
        $this->magazine  = $magazine;
        $this->reason    = $reason;

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReporting(): User
    {
        return $this->reporting;
    }

    public function getReported(): User
    {
        return $this->reported;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function increaseWeight(): self
    {
        $this->weight++;

        return $this;
    }

    public function getConsideredBy(): ?User
    {
        return $this->consideredBy;
    }

    public function setConsideredBy(User $consideredBy): self
    {
        $this->consideredBy = $consideredBy;

        return $this;
    }

    public function getConsideredAt(): ?\DateTime
    {
        return $this->consideredAt;
    }

    public function setConsideredAt(?\DateTime $consideredAt): self
    {
        $this->consideredAt = $consideredAt;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    abstract public function getType(): string;

    abstract public function getSubject(): ReportInterface;

    abstract public function clearSubject(): Report;
}

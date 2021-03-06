<?php declare(strict_types=1);

namespace App\Entity;

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
    const STATUS_REJECTED = 'accept';

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
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     */
    private User $consideredBy;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private ?\DateTime $consideredAt;

    /**
     * @ORM\Column(type="string")
     */
    private string $status = self::STATUS_PENDING;

    public function __construct(User $reporting, User $reported, Magazine $magazine)
    {
        $this->reporting = $reporting;
        $this->reported  = $reported;
        $this->magazine  = $magazine;

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

    public function getConsideredBy(): User
    {
        return $this->consideredBy;
    }

    public function setConsideredBy(User $consideredBy): void
    {
        $this->consideredBy = $consideredBy;
    }

    public function getConsideredAt(): ?\DateTime
    {
        return $this->consideredAt;
    }

    public function setConsideredAt(?\DateTime $consideredAt): void
    {
        $this->consideredAt = $consideredAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    abstract public function getType(): string;
}

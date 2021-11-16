<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Traits\CreatedAtTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
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
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Magazine", inversedBy="reports")
     */
    public Magazine $magazine;
    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reports")
     */
    public User $reporting;
    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="violations")
     */
    public User $reported;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public ?string $reason = null;
    /**
     * @ORM\Column(type="integer")
     */
    public int $weight = 1;
    /**
     * @ORM\JoinColumn(nullable=true)
     * @ORM\ManyToOne(targetEntity="User")
     */
    public ?User $consideredBy = null;
    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    public ?DateTime $consideredAt = null;
    /**
     * @ORM\Column(type="string")
     */
    public string $status = self::STATUS_PENDING;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

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

    public function increaseWeight(): self
    {
        $this->weight++;

        return $this;
    }

    abstract public function getType(): string;

    abstract public function getSubject(): ReportInterface;

    abstract public function clearSubject(): Report;
}

<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\ReportRepository;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: ReportRepository::class)]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'report_type', type: 'text')]
#[DiscriminatorMap([
    'entry' => 'EntryReport',
    'entry_comment' => 'EntryCommentReport',
    'post' => 'PostReport',
    'post_comment' => 'PostCommentReport',
])]
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

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'reports')]
    #[JoinColumn(nullable: false)]
    public Magazine $magazine;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'reports')]
    #[JoinColumn(nullable: false)]
    public User $reporting;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'violations')]
    #[JoinColumn(nullable: false)]
    public User $reported;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: true)]
    public ?User $consideredBy = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $reason = null;

    #[Column(type: 'integer', nullable: false)]
    public int $weight = 1;

    #[Column(type: 'datetimetz', nullable: true)]
    public ?DateTime $consideredAt = null;

    #[Column(type: 'string', nullable: false)]
    public string $status = self::STATUS_PENDING;

    public function __construct(User $reporting, User $reported, Magazine $magazine, ?string $reason = null)
    {
        $this->reporting = $reporting;
        $this->reported = $reported;
        $this->magazine = $magazine;
        $this->reason = $reason;

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

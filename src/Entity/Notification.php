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
 * })
 */
//   "entry": "EntryNotification",
//   "post": "PostNotification",
//   "post_comment": "PostCommentNotification",
abstract class Notification
{
    const STATUS_NEW = 'new';

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
     * @ORM\Column(type="string")
     */
    private string $status = self::STATUS_NEW;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;

        $this->createdAtTraitConstruct();
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
}

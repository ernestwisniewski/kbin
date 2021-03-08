<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\EntryCommentReport;
use App\Entity\EntryReport;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostCommentReport;
use App\Entity\PostReport;
use App\Entity\User;

class ReportDto
{
    private ?int $id = null;

    private ?Magazine $magazine = null;

    private ?User $reported = null;

    private ReportInterface $subject;

    private ?string $reason = null;

    public function create(ReportInterface $subject, ?string $reason = null, ?int $id = null): self
    {
        $this->id      = $id;
        $this->subject = $subject;
        $this->reason  = $reason;

        $this->magazine = $subject->getMagazine();
        $this->reported = $subject->getUser();

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function getReported(): ?User
    {
        return $this->reported;
    }

    public function getSubject(): ReportInterface
    {
        return $this->subject;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): ReportDto
    {
        $this->reason = $reason;

        return $this;
    }

    public function getReportClassName(): string
    {
        switch (get_class($this->getSubject())) {
            case Entry::class:
                return EntryReport::class;
            case EntryComment::class:
                return EntryCommentReport::class;
            case Post::class:
                return PostReport::class;
            case PostComment::class:
                return PostCommentReport::class;
        }

        throw new \LogicException();
    }

    public function getRouteName(): string
    {
        switch (get_class($this->getSubject())) {
            case Entry::class:
                return 'entry_report';
            case EntryComment::class:
                return 'entry_comment_report';
            case Post::class:
                return 'post_report';
            case PostComment::class:
                return 'post_comment_report';
        }

        throw new \LogicException();
    }
}

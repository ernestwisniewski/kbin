<?php declare(strict_types = 1);

namespace App\DTO;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use LogicException;

class ReportDto
{
    public ?Magazine $magazine = null;
    public ?User $reported = null;
    public ?ReportInterface $subject = null;
    public ?string $reason = null;
    private ?int $id = null;

    public function create(ReportInterface $subject, ?string $reason = null, ?int $id = null): self
    {
        $this->id      = $id;
        $this->subject = $subject;
        $this->reason  = $reason;

        $this->magazine = $subject->magazine;
        $this->reported = $subject->user;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

        throw new LogicException();
    }

    public function getSubject(): ReportInterface
    {
        return $this->subject;
    }
}

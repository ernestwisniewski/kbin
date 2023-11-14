<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\VisibilityInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class ReportResponseDto implements \JsonSerializable
{
    public ?MagazineSmallResponseDto $magazine = null;
    public ?UserSmallResponseDto $reported = null;
    public ?UserSmallResponseDto $reporting = null;
    #[OA\Property(oneOf: [
        new OA\Schema(ref: new Model(type: EntryResponseDto::class)),
        new OA\Schema(ref: new Model(type: EntryCommentResponseDto::class)),
        new OA\Schema(ref: new Model(type: PostResponseDto::class)),
        new OA\Schema(ref: new Model(type: PostCommentResponseDto::class)),
    ])]
    public ?\JsonSerializable $subject = null;
    public ?string $reason = null;
    public ?string $status = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $consideredAt = null;
    public ?UserSmallResponseDto $consideredBy = null;
    public ?int $weight = null;
    public ?int $reportId = null;

    public static function create(
        int $id = null,
        MagazineSmallResponseDto $magazine = null,
        UserSmallResponseDto $reported = null,
        UserSmallResponseDto $reporting = null,
        string $reason = null,
        string $status = null,
        int $weight = null,
        \DateTimeImmutable $createdAt = null,
        \DateTimeImmutable $consideredAt = null,
        UserSmallResponseDto $consideredBy = null,
    ): self {
        $dto = new ReportResponseDto();
        $dto->reportId = $id;
        $dto->magazine = $magazine;
        $dto->reported = $reported;
        $dto->reporting = $reporting;
        $dto->reason = $reason;
        $dto->status = $status;
        $dto->weight = $weight;
        $dto->createdAt = $createdAt;
        $dto->consideredAt = $consideredAt;
        $dto->consideredBy = $consideredBy;

        return $dto;
    }

    #[OA\Property(
        'type',
        enum: [
            'entry_report',
            'entry_comment_report',
            'post_report',
            'post_comment_report',
            'null_report',
        ]
    )]
    public function getType(): string
    {
        if (null === $this->subject) {
            // item was purged
            return 'null_report';
        }

        switch (\get_class($this->subject)) {
            case EntryResponseDto::class:
                return 'entry_report';
            case EntryCommentResponseDto::class:
                return 'entry_comment_report';
            case PostResponseDto::class:
                return 'post_report';
            case PostCommentResponseDto::class:
                return 'post_comment_report';
        }

        throw new \LogicException();
    }

    public function jsonSerialize(): mixed
    {
        $serializedSubject = null;
        if ($this->subject) {
            $visibility = $this->subject->getVisibility();
            $this->subject->visibility = VisibilityInterface::VISIBILITY_VISIBLE;
            $serializedSubject = $this->subject->jsonSerialize();
            $serializedSubject['visibility'] = $visibility;
        }

        return [
            'reportId' => $this->reportId,
            'type' => $this->getType(),
            'magazine' => $this->magazine->jsonSerialize(),
            'reason' => $this->reason,
            'reported' => $this->reported->jsonSerialize(),
            'reporting' => $this->reporting->jsonSerialize(),
            'subject' => $serializedSubject,
            'status' => $this->status,
            'weight' => $this->weight,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'consideredAt' => $this->consideredAt?->format(\DateTimeInterface::ATOM),
            'consideredBy' => $this->consideredBy?->jsonSerialize(),
        ];
    }
}

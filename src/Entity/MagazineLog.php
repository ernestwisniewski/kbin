<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: NotificationRepository::class)]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'log_type', type: 'text')]
#[DiscriminatorMap([
    'entry_deleted' => MagazineLogEntryDeleted::class,
    'entry_restored' => MagazineLogEntryRestored::class,
    'entry_comment_deleted' => MagazineLogEntryCommentDeleted::class,
    'entry_comment_restored' => MagazineLogEntryCommentRestored::class,
    'post_deleted' => MagazineLogPostDeleted::class,
    'post_restored' => MagazineLogPostRestored::class,
    'post_comment_deleted' => MagazineLogPostCommentDeleted::class,
    'post_comment_restored' => MagazineLogPostCommentRestored::class,
    'ban' => MagazineLogBan::class,
])]
abstract class MagazineLog
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'logs')]
    #[JoinColumn(nullable: false)]
    public Magazine $magazine;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public User $user;

    public function __construct(Magazine $magazine, User $user)
    {
        $this->magazine = $magazine;
        $this->user = $user;

        $this->createdAtTraitConstruct();
    }

    abstract public function getSubject(): ContentInterface|null;

    abstract public function clearSubject(): MagazineLog;

    abstract public function getType(): string;
}

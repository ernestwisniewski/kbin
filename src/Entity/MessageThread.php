<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\MessageThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use JetBrains\PhpStorm\Pure;

#[Entity(repositoryClass: MessageThreadRepository::class)]
class MessageThread
{
    use UpdatedAtTrait;

    #[JoinTable(
        name: 'message_thread_participants',
        joinColumns: [
            new JoinColumn(name: 'message_thread_id', referencedColumnName: 'id'),
        ],
        inverseJoinColumns: [
            new JoinColumn(name: 'user_id', referencedColumnName: 'id'),
        ]
    )]
    #[ManyToMany(targetEntity: User::class)]
    public Collection $participants;
    #[OneToMany(mappedBy: 'thread', targetEntity: Message::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[OrderBy(['createdAt' => 'DESC'])]
    public Collection $messages;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[Pure]
    public function __construct(User ...$participants)
    {
        $this->participants = new ArrayCollection($participants);
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOtherParticipants(User $self): array
    {
        return $this->participants->filter(
            static function (User $user) use ($self) {
                return $user !== $self;
            }
        )->getValues();
    }

    public function getNewMessages(User $user): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', Message::STATUS_NEW))
            ->andWhere(Criteria::expr()->neq('sender', $user));

        return $this->messages->matching($criteria);
    }

    public function countNewMessages(User $user): int
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', Message::STATUS_NEW))
            ->andWhere(Criteria::expr()->neq('sender', $user));

        return $this->messages->matching($criteria)->count();
    }

    public function addMessage(Message $message): void
    {
        if (!$this->messages->contains($message)) {
            if (!$this->userIsParticipant($message->sender)) {
                throw new \DomainException('Sender is not allowed to participate');
            }

            $this->messages->add($message);
        }
    }

    public function userIsParticipant($user): bool
    {
        return $this->participants->contains($user);
    }

    public function removeMessage(Message $message): void
    {
        $this->messages->removeElement($message);
    }

    public function getTitle(): string
    {
        $body = $this->messages[0]->body;
        $firstLine = preg_replace('/^# |\R.*/', '', $body);

        if (grapheme_strlen($firstLine) <= 80) {
            return $firstLine;
        }

        return grapheme_substr($firstLine, 0, 80).'…';
    }
}

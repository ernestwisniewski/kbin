<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\MessageBus;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\DomainBlock;
use App\Entity\DomainSubscription;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Favourite;
use App\Entity\MagazineBan;
use App\Entity\MagazineBlock;
use App\Entity\MagazineOwnershipRequest;
use App\Entity\MagazineSubscription;
use App\Entity\Message;
use App\Entity\Moderator;
use App\Entity\ModeratorRequest;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\Report;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\Kbin\Entry\EntryDelete;
use App\Kbin\Entry\EntryPurge;
use App\Kbin\EntryComment\EntryCommentDelete;
use App\Kbin\EntryComment\EntryCommentPurge;
use App\Kbin\Magazine\MagazineUnblock;
use App\Kbin\Magazine\MagazineUnsubscribe;
use App\Kbin\MessageBus\Contracts\AsyncMessageInterface;
use App\Kbin\Post\PostDelete;
use App\Kbin\Post\PostPurge;
use App\Kbin\PostComment\PostCommentDelete;
use App\Kbin\PostComment\PostCommentPurge;
use App\Kbin\User\UserAvatarDetach;
use App\Kbin\User\UserCoverDetach;
use App\Kbin\User\UserUnblock;
use App\Kbin\User\UserUnfollow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class UserDeleteHandler implements AsyncMessageInterface
{
    private ?User $user;
    private int $batchSize = 5;
    private string $op;

    public function __construct(
        private readonly UserAvatarDetach $userAvatarDetach,
        private readonly UserCoverDetach $userCoverDetach,
        private readonly UserUnfollow $userUnfollow,
        private readonly UserUnblock $userUnblock,
        private readonly MagazineUnsubscribe $magazineUnsubscribe,
        private readonly MagazineUnblock $magazineUnblock,
        private readonly EntryCommentDelete $entryCommentDelete,
        private readonly EntryCommentPurge $entryCommentPurge,
        private readonly EntryDelete $entryDelete,
        private readonly EntryPurge $entryPurge,
        private readonly PostCommentDelete $postCommentDelete,
        private readonly PostCommentPurge $postCommentPurge,
        private readonly PostDelete $postDelete,
        private readonly PostPurge $postPurge,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(UserDeleteMessage $message): void
    {
        $this->user = $this->entityManager
            ->getRepository(User::class)
            ->find($message->id);

        $this->op = $message->purge ? 'purge' : 'delete';

        if (!$this->user) {
            throw new UnrecoverableMessageHandlingException('User not found');
        }

        $this->removeMeta();
        $retry = $this->removeMagazineSubscriptions()
            || $this->removeMagazineBlocks()
            || $this->removeUserFollows()
            || $this->removeUserBlocks()
            || $this->removeReports()
            || $this->removeEntryComments()
            || $this->removeEntries()
            || $this->removePostComments()
            || $this->removePosts()
            || $this->removeMessages();

        if ($retry) {
            $this->bus->dispatch($message);
        } else {
            $this->removeDomainSubscriptions();
            $this->removeDomainBlocks();
            $this->removeNotifications();
            $this->removeVotes();
            $this->purgeVotes();
            $this->removeFavourites();
            $this->removeFollowing();
            $this->removeMod();
            $this->removeBans();
            $this->removeMessagesParticipants();
            $this->removeModeratorRequests();
            $this->removeModeratorOwnershipRequests();

            if ($message->contentOnly) {
                return;
            }

            $this->user = $this->entityManager
                ->getRepository(User::class)
                ->find($message->id);

            if ('purge' === $this->op) {
                $this->entityManager->remove($this->user);
                $this->entityManager->flush();
            } else {
                $this->user->username = '!deleted'.$this->user->getId();
                $this->user->email = '!deleted'.$this->user->getId().'@kbin.del';
                $this->user->isVerified = false;
                $this->user->isDeleted = true;
                $this->user->visibility = VisibilityInterface::VISIBILITY_SOFT_DELETED;

                $this->entityManager->persist($this->user);
                $this->entityManager->flush();
            }
        }
    }

    private function removeMeta(): void
    {
        if (!$this->user->isAccountDeleted()) {
            ($this->userAvatarDetach)($this->user);
            ($this->userCoverDetach)($this->user);
            $this->user->isDeleted = true;
            $this->user->about = null;
        }
    }

    private function removeMagazineSubscriptions(): bool
    {
        $subscriptions = $this->entityManager
            ->getRepository(MagazineSubscription::class)
            ->findBy(
                [
                    'user' => $this->user,
                ],
                ['createdAt' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($subscriptions as $subscription) {
            $retry = true;

            ($this->magazineUnsubscribe)($subscription->magazine, $this->user);
        }

        return $retry;
    }

    private function removeMagazineBlocks(): bool
    {
        $subscriptions = $this->entityManager
            ->getRepository(MagazineBlock::class)
            ->findBy(
                [
                    'user' => $this->user,
                ],
                ['createdAt' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($subscriptions as $subscription) {
            $retry = true;

            ($this->magazineUnblock)($subscription->magazine, $this->user);
        }

        return $retry;
    }

    private function removeUserFollows(): bool
    {
        $subscriptions = $this->entityManager
            ->getRepository(UserFollow::class)
            ->findBy(
                [
                    'follower' => $this->user,
                ],
                ['createdAt' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($subscriptions as $subscription) {
            $retry = true;

            ($this->userUnfollow)($this->user, $subscription->following);
        }

        return $retry;
    }

    private function removeUserBlocks(): bool
    {
        $subscriptions = $this->entityManager
            ->getRepository(UserBlock::class)
            ->findBy(
                [
                    'blocker' => $this->user,
                ],
                ['createdAt' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($subscriptions as $subscription) {
            $retry = true;

            ($this->userUnblock)($this->user, $subscription->blocked);
        }

        return $retry;
    }

    private function removeVotes(): void
    {
        foreach ([Entry::class, Post::class, EntryComment::class, PostComment::class] as $subjectClass) {
            $query = $this->entityManager->createQuery(
                'DELETE FROM '.$subjectClass.'Vote v WHERE v.user = :user'
            );
            $query->setParameter('user', $this->user->getId());
            $query->execute();
        }
    }

    private function removeEntryComments(): bool
    {
        if ('purge' === $this->op) {
            $comments = $this->entityManager
                ->getRepository(EntryComment::class)
                ->findBy(
                    [
                        'user' => $this->user,
                    ],
                    ['id' => 'DESC'],
                    $this->batchSize
                );
        } else {
            $comments = $this->entityManager
                ->getRepository(EntryComment::class)
                ->findToDelete($this->user, $this->batchSize);
        }

        $retry = false;

        foreach ($comments as $comment) {
            $retry = true;
            if ('purge' === $this->op) {
                ($this->entryCommentPurge)($this->user, $comment);
            } else {
                ($this->entryCommentDelete)($this->user, $comment);
            }
        }

        return $retry;
    }

    private function removeEntries(): bool
    {
        if ('purge' === $this->op) {
            $entries = $this->entityManager
                ->getRepository(Entry::class)
                ->findBy(
                    [
                        'user' => $this->user,
                    ],
                    ['id' => 'DESC'],
                    $this->batchSize
                );
        } else {
            $entries = $this->entityManager
                ->getRepository(Entry::class)
                ->findToDelete($this->user, $this->batchSize);
        }

        $retry = false;

        foreach ($entries as $entry) {
            $retry = true;
            if ('purge' === $this->op) {
                ($this->entryPurge)($this->user, $entry);
            } else {
                ($this->entryDelete)($this->user, $entry);
            }
        }

        return $retry;
    }

    private function removePostComments(): bool
    {
        if ('purge' === $this->op) {
            $comments = $this->entityManager
                ->getRepository(PostComment::class)
                ->findBy(
                    [
                        'user' => $this->user,
                    ],
                    ['id' => 'DESC'],
                    $this->batchSize
                );
        } else {
            $comments = $this->entityManager
                ->getRepository(PostComment::class)
                ->findToDelete($this->user, $this->batchSize);
        }

        $retry = false;

        foreach ($comments as $comment) {
            $retry = true;
            if ('purge' === $this->op) {
                ($this->postCommentPurge)($this->user, $comment);
            } else {
                ($this->postCommentDelete)($this->user, $comment);
            }
        }

        return $retry;
    }

    private function removePosts(): bool
    {
        if ('purge' === $this->op) {
            $posts = $this->entityManager
                ->getRepository(Post::class)
                ->findBy(
                    [
                        'user' => $this->user,
                    ],
                    ['id' => 'DESC'],
                    $this->batchSize
                );
        } else {
            $posts = $this->entityManager
                ->getRepository(Post::class)
                ->findToDelete($this->user, $this->batchSize);
        }

        $retry = false;

        foreach ($posts as $post) {
            $retry = true;
            if ('purge' === $this->op) {
                ($this->postPurge)($this->user, $post);
            } else {
                ($this->postDelete)($this->user, $post);
            }
        }

        return $retry;
    }

    private function removeMessages(): bool
    {
        $messages = $this->entityManager
            ->getRepository(Message::class)
            ->findBy(
                [
                    'sender' => $this->user,
                ],
                ['createdAt' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($messages as $message) {
            $retry = true;

            $message->thread->removeMessage($message);

            if (0 === \count($message->thread->messages)) {
                $this->entityManager->remove($message->thread);
            }
        }

        $this->entityManager->flush();

        return $retry;

        //        $em = $this->entityManager;
        //        $query = $em->createQuery('DELETE FROM '.Message::class.' m WHERE m.sender = :userId');
        //        $query->setParameter('userId', $this->user->getId());
        //        $query->execute();
        //
        //        $this->entityManager->flush();
    }

    private function removeFavourites(): void
    {
        $query = $this->entityManager->createQuery(
            'DELETE FROM '.Favourite::class.' f WHERE f.user = :user'
        );
        $query->setParameter('user', $this->user->getId());
        $query->execute();
    }

    private function removeFollowing(): void
    {
        $query = $this->entityManager->createQuery(
            'DELETE FROM '.UserFollow::class.' f WHERE f.following = :user OR f.follower = :user'
        );
        $query->setParameter('user', $this->user->getId());
        $query->execute();
    }

    private function removeNotifications(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery('DELETE FROM '.Notification::class.' n WHERE n.user = :userId');
        $query->setParameter('userId', $this->user->getId());
        $query->execute();
    }

    private function removeDomainSubscriptions(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery('DELETE FROM '.DomainSubscription::class.' s WHERE s.user = :userId');
        $query->setParameter('userId', $this->user->getId());
        $query->execute();
    }

    private function removeDomainBlocks(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery('DELETE FROM '.DomainBlock::class.' b WHERE b.user = :userId');
        $query->setParameter('userId', $this->user->getId());
        $query->execute();
    }

    private function removeMod(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery('DELETE FROM '.Moderator::class.' m WHERE m.user = :userId AND m.isOwner = false');
        $query->setParameter('userId', $this->user->getId());
        $query->execute();

        $admin = $this->entityManager->getRepository(User::class)->findAdmin();

        $query = $em->createQuery(
            'UPDATE '.Moderator::class.' m SET m.user = :newUserId WHERE m.user = :userId'
        );
        $query->setParameters(['userId' => $this->user->getId(), 'newUserId' => $admin->getId()]);
        $query->execute();
    }

    private function removeReports(): bool
    {
        $em = $this->entityManager;
        $query = $em->createQuery(
            'DELETE FROM '.Report::class.' r WHERE r.reported = :userId OR r.reporting = :userId'
        );
        $query->setParameter('userId', $this->user->getId());
        $query->execute();

        return false;
    }

    private function purgeVotes(): void
    {
        foreach ([Entry::class, Post::class, EntryComment::class, PostComment::class] as $subjectClass) {
            $query = $this->entityManager->createQuery(
                'DELETE FROM '.$subjectClass.'Vote v WHERE v.user = :user OR v.author = :user'
            );
            $query->setParameter('user', $this->user->getId());
            $query->execute();
        }
    }

    private function removeBans(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery(
            'DELETE FROM '.MagazineBan::class.' b WHERE b.user = :userId OR b.bannedBy = :userId'
        );
        $query->setParameter('userId', $this->user->getId());
        $query->execute();
    }

    private function removeMessagesParticipants(): void
    {
        $conn = $this->entityManager->getConnection();
        $sql = 'DELETE FROM message_thread_participants AS mp WHERE mp.user_id = :userId';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $this->user->getId());

        $stmt->executeQuery();
    }

    private function removeModeratorRequests(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery(
            'DELETE FROM '.ModeratorRequest::class.' r WHERE r.user = :userId'
        );
        $query->setParameter('userId', $this->user->getId());
        $query->execute();
    }

    private function removeModeratorOwnershipRequests(): void
    {
        $em = $this->entityManager;
        $query = $em->createQuery(
            'DELETE FROM '.MagazineOwnershipRequest::class.' r WHERE r.user = :userId'
        );
        $query->setParameter('userId', $this->user->getId());
        $query->execute();
    }
}

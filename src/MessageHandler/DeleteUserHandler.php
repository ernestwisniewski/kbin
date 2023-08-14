<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Contracts\VotableInterface;
use App\Entity\DomainBlock;
use App\Entity\DomainSubscription;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Favourite;
use App\Entity\MagazineBan;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Message;
use App\Entity\Moderator;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\Report;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\Message\DeleteUserMessage;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\FavouriteManager;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Service\UserManager;
use App\Service\VoteManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteUserHandler
{
    private ?User $user;
    private int $batchSize = 5;
    private string $op;

    public function __construct(
        private readonly UserManager $userManager,
        private readonly MagazineManager $magazineManager,
        private readonly EntryCommentManager $entryCommentManager,
        private readonly EntryManager $entryManager,
        private readonly PostCommentManager $postCommentManager,
        private readonly PostManager $postManager,
        private readonly VoteManager $voteManager,
        private readonly FavouriteManager $favouriteManager,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(DeleteUserMessage $message): void
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
            || $this->removeFavourites()
            || $this->removeNotifications()
            || $this->removeReports()
            || $this->removeVotes()
            || $this->removeEntryComments()
            || $this->removeEntries()
            || $this->removePostComments()
            || $this->removePosts()
            || $this->removeMessages();

        $this->entityManager->clear();

        if ($retry) {
            $this->bus->dispatch($message);
        } else {
            $this->removeDomainSubscriptions();
            $this->removeDomainBlocks();
            $this->purgeVotes();
            $this->removeMod();
            $this->removeBans();
            $this->removeMessagesParticipants();

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

                $this->entityManager->persist($this->user);
                $this->entityManager->flush();
            }
        }
    }

    private function removeMeta(): void
    {
        if (!$this->user->isAccountDeleted()) {
            $this->userManager->detachAvatar($this->user);
            $this->userManager->detachCover($this->user);
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

            $this->magazineManager->unsubscribe($subscription->magazine, $this->user);
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

            $this->magazineManager->unblock($subscription->magazine, $this->user);
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

            $this->userManager->unfollow($this->user, $subscription->following);
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

            $this->userManager->unblock($this->user, $subscription->blocked);
        }

        return $retry;
    }

    private function removeVotes(): bool
    {
        foreach ([Entry::class, Post::class, EntryComment::class, PostComment::class] as $subjectClass) {
            $subjects = $this->entityManager->createQueryBuilder()
                ->select('c')
                ->from($subjectClass, 'c')
                ->join('c.votes', 'cv')
                ->where('cv.user = :user')
                ->andWhere('cv.choice != 0')
                ->orderBy('c.id', 'DESC')
                ->setParameter('user', $this->user)
                ->setMaxResults($this->batchSize)
                ->getQuery()
                ->execute();

            $retry = false;

            foreach ($subjects as $subject) {
                $retry = true;
                $this->voteManager->vote(VotableInterface::VOTE_NONE, $subject, $this->user);
            }
        }

        return $retry;
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
            if ('delete' === $this->op) {
                $this->entryCommentManager->{$this->op}($this->user, $comment);
            } else {
                $this->entryCommentManager->{$this->op}($comment);
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
            if ('delete' === $this->op) {
                $this->entryManager->{$this->op}($this->user, $entry);
            } else {
                $this->entryManager->{$this->op}($entry);
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
            if ('delete' === $this->op) {
                $this->postCommentManager->{$this->op}($this->user, $comment);
            } else {
                $this->postCommentManager->{$this->op}($comment);
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
            if ('delete' === $this->op) {
                $this->postManager->{$this->op}($this->user, $post);
            } else {
                $this->postManager->{$this->op}($post);
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

            if (0 === count($message->thread->messages)) {
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

    private function removeFavourites(): bool
    {
        $subjects = $this->entityManager->getRepository(Favourite::class)->findByUser($this->user);

        $retry = false;

        foreach ($subjects as $subject) {
            $retry = true;
            $this->favouriteManager->toggle($this->user, $subject->getSubject(), FavouriteManager::TYPE_UNLIKE);
        }

        return $retry;
    }

    private function removeNotifications(): bool
    {
        $em = $this->entityManager;
        $query = $em->createQuery('DELETE FROM '.Notification::class.' n WHERE n.user = :userId');
        $query->setParameter('userId', $this->user->getId());
        $query->execute();

        return false;
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
}

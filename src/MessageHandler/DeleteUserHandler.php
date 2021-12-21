<?php declare(strict_types = 1);

namespace App\MessageHandler;

use App\Entity\Contracts\VoteInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollow;
use App\Message\DeleteUserMessage;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Service\UserManager;
use App\Service\VoteManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class DeleteUserHandler implements MessageHandlerInterface
{
    private ?User $user;
    private int $batchSize = 5;
    private string $op;

    public function __construct(
        private UserManager $userManager,
        private MagazineManager $magazineManager,
        private EntryCommentManager $entryCommentManager,
        private EntryManager $entryManager,
        private PostCommentManager $postCommentManager,
        private PostManager $postManager,
        private VoteManager $voteManager,
        private MessageBusInterface $bus,
        private EntityManagerInterface $entityManager
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

        $retry =
            $this->removeMeta()
//            || $this->removeNotifications()
            || $this->removeMagazineSubscriptions()
            || $this->removeMagazineBlocks()
            || $this->removeUserFollows()
            || $this->removeUserBlocks()
            || $this->removeVotes(EntryComment::class)
            || $this->removeEntryComments()
            || $this->removeVotes(Entry::class)
            || $this->removeEntries()
            || $this->removeVotes(PostComment::class)
            || $this->removePostComments()
            || $this->removeVotes(Post::class)
            || $this->removePosts()
            || $this->removeMessages();

        $this->entityManager->clear();

        if ($retry) {
            $this->bus->dispatch($message);
        }
    }

    private function removeMeta(): bool
    {
        if ($this->user->isAccountDeleted()) {
            return false;
        }

        $this->user->username = '!deleted'.$this->user->getId();
        $this->user->email    = '!deleted'.$this->user->getId().'@karab.in';

        return false;
    }

    private function removeNotifications(): bool
    {
        $notifications = $this->entityManager
            ->getRepository(Notification::class)
            ->findBy(
                [
                    'user' => $this->user,
                ],
                ['createdAt' => 'DESC'],
                $this->batchSize
            );

        $retry = false;

        foreach ($notifications as $notification) {
            $retry = true;

            $this->entityManager->remove($notification);
        }

        $this->entityManager->flush();

        return $retry;
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

    private function removeVotes(string $subjectClass): bool
    {
        $subjects = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from($subjectClass, 'c')
            ->join('c.votes', 'cv')
            ->where('cv.user = :user')
            ->orderBy('c.id', 'DESC')
            ->setParameter('user', $this->user)
            ->setMaxResults($this->batchSize)
            ->getQuery()
            ->execute();

        $retry = false;

        try {
            $this->entityManager->beginTransaction();

            foreach ($subjects as $subject) {
                $retry = true;

                $this->voteManager->vote(VoteInterface::VOTE_NONE, $subject, $this->user);
            }

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            throw $e;
        }

        return $retry;
    }

    private function removeEntryComments(): bool
    {
        if ($this->op === 'purge') {
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

        try {
            $this->entityManager->beginTransaction();

            foreach ($comments as $comment) {
                $retry = true;
                if($this->op === 'delete') {
                    $this->entryCommentManager->{$this->op}($this->user, $comment);
                } else {
                    $this->entryCommentManager->{$this->op}($comment);
                }
            }

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $retry;
    }

    private function removeEntries(): bool
    {
        if ($this->op === 'purge') {
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

        try {
            $this->entityManager->beginTransaction();

            foreach ($entries as $entry) {
                $retry = true;
                if($this->op === 'delete') {
                    $this->entryManager->{$this->op}($this->user, $entry);
                } else {
                    $this->entryManager->{$this->op}($entry);
                }
            }

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $retry;
    }

    private function removePostComments(): bool
    {
        if ($this->op === 'purge') {
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

        try {
            $this->entityManager->beginTransaction();

            foreach ($comments as $comment) {
                $retry = true;
                if($this->op === 'delete') {
                    $this->postCommentManager->{$this->op}($this->user, $comment);
                } else {
                    $this->postCommentManager->{$this->op}($comment);
                }
            }

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $retry;
    }

    private function removePosts(): bool
    {
        if ($this->op === 'purge') {
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

        try {
            $this->entityManager->beginTransaction();

            foreach ($posts as $post) {
                $retry = true;
                if($this->op === 'delete') {
                    $this->postManager->{$this->op}($this->user, $post);
                } else {
                    $this->postManager->{$this->op}($post);
                }
            }

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
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

            if (count($message->thread->messages) === 0) {
                $this->entityManager->remove($message->thread);
            }
        }

        $this->entityManager->flush();

        return $retry;
    }
}

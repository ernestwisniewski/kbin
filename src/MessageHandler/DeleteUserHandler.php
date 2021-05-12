<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Contracts\VoteInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Message\DeleteUserMessage;
use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Service\VoteManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBus;

class DeleteUserHandler
{
    private ?User $user;
    private int $batchSize = 50;
    private string $op;

    public function __construct(
        private UserRepository $userRepository,
        private EntryCommentManager $entryCommentManager,
        private EntryCommentRepository $entryCommentRepository,
        private EntryManager $entryManager,
        private EntryRepository $entryRepository,
        private PostCommentManager $postCommentManager,
        private PostCommentRepository $postCommentRepository,
        private PostManager $postManager,
        private PostRepository $postRepository,
        private VoteManager $voteManager,
        private MessageRepository $messageRepository,
        private NotificationRepository $notificationRepository,
        private MessageBus $bus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(DeleteUserMessage $message): void
    {
        $this->user = $this->userRepository->find($message->id);
        $this->op   = $message->purge ? 'purge' : 'delete';

        if (!$this->user) {
            throw new UnrecoverableMessageHandlingException('User not found');
        }

        $retry =
            $this->removeMeta()
            || $this->removeEntryComments()
            || $this->removeVotes(EntryComment::class)
            || $this->removeEntries()
            || $this->removeVotes(Entry::class)
            || $this->removePostComments()
            || $this->removeVotes(PostComment::class)
            || $this->removePosts()
            || $this->removeVotes(Post::class)
            || $this->removeMessages()
            || $this->removeNotifications();

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

        return true;
    }

    private function removeEntryComments(): bool
    {
        $comments = $this->entryCommentRepository->findBy(
            [
                'user' => $this->user,
            ],
            ['id' => 'DESC'],
            $this->batchSize
        );

        $retry = false;

        try {
            $this->entityManager->beginTransaction();

            foreach ($comments as $comment) {
                $retry = true;
                $this->entryCommentManager->{$this->op}($comment);
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
        $entries = $this->entryRepository->findBy(
            [
                'user' => $this->user,
            ],
            ['id' => 'DESC'],
            $this->batchSize
        );

        $retry = false;

        try {
            $this->entityManager->beginTransaction();

            foreach ($entries as $entry) {
                $retry = true;
                $this->entryManager->{$this->op}($entry);
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
        $comments = $this->postCommentRepository->findBy(
            [
                'user' => $this->user,
            ],
            ['id' => 'DESC'],
            $this->batchSize
        );

        $retry = false;

        try {
            $this->entityManager->beginTransaction();

            foreach ($comments as $comment) {
                $retry = true;
                $this->postCommentManager->{$this->op}($comment);
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
        $posts = $this->postRepository->findBy(
            [
                'user' => $this->user,
            ],
            ['id' => 'DESC'],
            $this->batchSize
        );

        $retry = false;

        try {
            $this->entityManager->beginTransaction();

            foreach ($posts as $post) {
                $retry = true;
                $this->postManager->{$this->op}($post);
            }

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
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
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw $e;
        }

        return $retry;
    }

    private function removeMessages(): bool
    {
        $messages = $this->messageRepository->findBy(
            [
                'sender' => $this->user,
            ],
            ['timestamp' => 'DESC'],
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

    private function removeNotifications(): bool
    {
        $notifications = $this->notificationRepository->findBy(
            [
                'user' => $this->user,
            ],
            ['timestamp' => 'DESC'],
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
}

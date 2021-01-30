<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\EntryCommentFactory;
use Webmozart\Assert\Assert;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\User;

class EntryCommentManager
{
    private EntryCommentFactory $commentFactory;
    private EntryCommentRepository $commentRepository;
    private EntryRepository $entryRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntryCommentFactory $commentFactory,
        EntryCommentRepository $commentRepository,
        EntryRepository $entryRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->commentFactory    = $commentFactory;
        $this->commentRepository = $commentRepository;
        $this->entryRepository   = $entryRepository;
        $this->entityManager     = $entityManager;
    }

    public function createComment(EntryCommentDto $commentDto, User $user): EntryComment
    {
        $comment = $this->commentFactory->createFromDto($commentDto, $user);

        $entry    = $comment->getEntry();
        $magazine = $entry->getMagazine();

        $entry->addComment($comment);

        $magazine->setCommentCount(
            $this->entryRepository->countCommentsByMagazine($magazine) + 1
        );

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    public function editComment(EntryComment $comment, EntryCommentDto $commentDto): EntryComment
    {
        Assert::same($comment->getEntry()->getId(), $commentDto->getEntry()->getId());

        $comment->setBody($commentDto->getBody());

        $this->entityManager->flush();

        return $comment;
    }

    public function createCommentDto(EntryComment $comment): EntryCommentDto
    {
        return $this->commentFactory->createDto($comment);
    }

    public function purgeComment(EntryComment $comment): void
    {
        $entry    = $comment->getEntry();
        $magazine = $entry->getMagazine();

        $entry->setCommentCount(
            $entry->getComments()->count() - 1
        );
        $magazine->setCommentCount(
            $this->entryRepository->countCommentsByMagazine($magazine) - 1
        );

        $this->entityManager->remove($comment);

        $this->entityManager->flush();
    }
}

<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\UserNoteDto;
use App\Entity\User;
use App\Entity\UserNote;
use App\Repository\UserNoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserNoteManager
{
    public function __construct(private UserNoteRepository $repository, private EntityManagerInterface $entityManager)
    {
    }

    public function save(User $user, User $target, string $body): UserNote
    {
        $this->clear($user, $target);

        $note = new UserNote($user, $target, $body);

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return $note;
    }

    public function createDto(User $user, User $target): UserNoteDto
    {
        $dto         = new UserNoteDto();
        $dto->target = $target;

        $note = $this->repository->findOneBy([
            'user'   => $user,
            'target' => $target,
        ]);

        if ($note) {
            $dto->body = $note->body;
        }

        return $dto;
    }

    public function clear(User $user, User $target): void
    {
        $note = $this->repository->findOneBy([
            'user'   => $user,
            'target' => $target,
        ]);

        if ($note) {
            $this->entityManager->remove($note);
            $this->entityManager->flush();
        }
    }
}

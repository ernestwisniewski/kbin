<?php

declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\PostComment;
use App\Repository\PostCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:post:comments:root:update',
    description: 'This command allows generate root id for comments.',
)]
class PostCommentRootUpdateCommand extends Command
{
    public function __construct(
        private readonly PostCommentRepository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queryBuilder = $this->repository->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.parent IS NOT NULL')
            ->andWhere('c.root IS NULL')
            ->andWhere('c.updateMark = false')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($queryBuilder as $comment) {
            echo $comment['id'].PHP_EOL;
            $this->update($this->repository->find($comment['id']));
        }

        return Command::SUCCESS;
    }

    private function update(PostComment $comment)
    {
        if (null === $comment->parent->root) {
            $this->entityManager->getConnection()->executeQuery(
                'UPDATE post_comment SET root_id = :root_id, update_mark = true WHERE id = :id',
                [
                    'root_id' => $comment->parent->getId(),
                    'id' => $comment->getId(),
                ]
            );

            return;
        }

        $this->entityManager->getConnection()->executeQuery(
            'UPDATE post_comment SET root_id = :root_id, update_mark = true WHERE id = :id',
            [
                'root_id' => $comment->parent->root->getId(),
                'id' => $comment->getId(),
            ]
        );
    }
}

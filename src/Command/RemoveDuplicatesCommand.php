<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'kbin:post:remove-duplicates')]
class RemoveDuplicatesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->removePosts();
        $this->removeActors();

        return Command::SUCCESS;
    }

    private function removePosts()
    {
        $conn = $this->entityManager->getConnection();
        $sql = "
                SELECT *
                FROM post
                WHERE ap_id IN (
                  SELECT ap_id
                  FROM post
                  GROUP BY ap_id
                  HAVING COUNT(*) > 1
                 )
        ";
        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        $results = $stmt->fetchAllAssociative();

        foreach ($results as $item) {
            try {
                $post = $this->entityManager->getRepository(Post::class)->find($item['id']);
                $this->entityManager->remove($post);
                $this->entityManager->flush();
            } catch (\Exception $e) {
            }
        }
    }

    private function removeActors()
    {
        $conn = $this->entityManager->getConnection();
        $sql = '
                SELECT *
                FROM "user"
                WHERE ap_id IN (
                  SELECT ap_id
                  FROM "user"
                  GROUP BY ap_id
                  HAVING COUNT(*) > 1
                 )
        ';
        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        $results = $stmt->fetchAllAssociative();

        foreach ($results as $item) {
//            $this->entityManager->beginTransaction();

            try {
                $user = $this->entityManager->getRepository(User::class)->find($item['id']);
                if ($user->posts->count() || $user->postComments->count() || $user->follows->count(
                    ) || $user->followers->count()) {
                    continue;
                }
                $this->entityManager->remove($user);
                $this->entityManager->flush();
            } catch (\Exception $e) {
//                $this->entityManager->rollback();

                var_dump($e->getMessage());
            }
        }
    }
}

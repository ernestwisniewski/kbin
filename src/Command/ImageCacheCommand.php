<?php declare(strict_types = 1);

namespace App\Command;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ImageCacheCommand extends Command
{
    protected static $defaultName = 'kbin:cache:build';

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('This command allows you to rebuild image thumbs cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->buildUsersCache();
        $this->buildEntriesCache();
        $this->buildEntryCommentsCache();
        $this->buildPostsCache();
        $this->buildPostCommentsCache();
        $this->buildMagazinesCache();

        return 1;
    }

    private function buildUsersCache(): void
    {
        $repo = $this->entityManager->getRepository(User::class);
        $res  = $repo->createQueryBuilder('u')->select('u')
            ->join('u.avatar', 'i')
            ->getQuery()
            ->getResult();

        foreach ($res as $user) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths'    => [$user->avatar->filePath],
                '--filter' => ['avatar_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildEntriesCache(): void
    {
        $repo = $this->entityManager->getRepository(Entry::class);
        $res  = $repo->createQueryBuilder('e')->select('e')
            ->join('e.image', 'i')
            ->getQuery()
            ->getResult();

        foreach ($res as $entry) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths'    => [$entry->image->filePath],
                '--filter' => ['entry_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildEntryCommentsCache(): void
    {
        $repo = $this->entityManager->getRepository(EntryComment::class);
        $res  = $repo->createQueryBuilder('c')->select('c')
            ->join('c.image', 'i')
            ->getQuery()
            ->getResult();

        foreach ($res as $comment) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths'    => [$comment->image->filePath],
                '--filter' => ['post_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildPostsCache(): void
    {
        $repo = $this->entityManager->getRepository(Post::class);
        $res  = $repo->createQueryBuilder('p')->select('p')
            ->join('p.image', 'i')
            ->getQuery()
            ->getResult();

        foreach ($res as $post) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths'    => [$post->image->filePath],
                '--filter' => ['post_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildPostCommentsCache(): void
    {
        $repo = $this->entityManager->getRepository(PostComment::class);
        $res  = $repo->createQueryBuilder('c')->select('c')
            ->join('c.image', 'i')
            ->getQuery()
            ->getResult();

        foreach ($res as $comment) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths'    => [$comment->image->filePath],
                '--filter' => ['post_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildMagazinesCache(): void
    {
        $repo = $this->entityManager->getRepository(Magazine::class);
        $res  = $repo->createQueryBuilder('m')->select('m')
            ->join('m.cover', 'i')
            ->getQuery()
            ->getResult();

        foreach ($res as $magazine) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths'    => [$magazine->cover->filePath],
                '--filter' => ['post_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }
}

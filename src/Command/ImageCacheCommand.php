<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:cache:build',
    description: 'This command allows you to rebuild image thumbs cache.'
)]
class ImageCacheCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
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
        $res = $repo->createQueryBuilder('u')->select('i.filePath')
            ->join('u.avatar', 'i')
            ->getQuery()
            ->getArrayResult();

        foreach ($res as $image) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths' => [$image['filePath']],
                '--filter' => ['avatar_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildEntriesCache(): void
    {
        $repo = $this->entityManager->getRepository(Entry::class);
        $res = $repo->createQueryBuilder('e')->select('i.filePath')
            ->join('e.image', 'i')
            ->getQuery()
            ->getArrayResult();

        foreach ($res as $image) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths' => [$image['filePath']],
                '--filter' => ['entry_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildEntryCommentsCache(): void
    {
        $repo = $this->entityManager->getRepository(EntryComment::class);
        $res = $repo->createQueryBuilder('c')->select('i.filePath')
            ->join('c.image', 'i')
            ->getQuery()
            ->getArrayResult();

        foreach ($res as $image) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths' => [$image['filePath']],
                '--filter' => ['post_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildPostsCache(): void
    {
        $repo = $this->entityManager->getRepository(Post::class);
        $res = $repo->createQueryBuilder('p')->select('i.filePath')
            ->join('p.image', 'i')
            ->getQuery()
            ->getArrayResult();

        foreach ($res as $image) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths' => [$image['filePath']],
                '--filter' => ['post_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildPostCommentsCache(): void
    {
        $repo = $this->entityManager->getRepository(PostComment::class);
        $res = $repo->createQueryBuilder('c')->select('i.filePath')
            ->join('c.image', 'i')
            ->getQuery()
            ->getArrayResult();

        foreach ($res as $image) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths' => [$image['filePath']],
                '--filter' => ['post_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }

    private function buildMagazinesCache(): void
    {
        $repo = $this->entityManager->getRepository(Magazine::class);
        $res = $repo->createQueryBuilder('m')->select('i.filePath')
            ->join('m.icon', 'i')
            ->getQuery()
            ->getArrayResult();

        foreach ($res as $image) {
            $command = $this->getApplication()->find('liip:imagine:cache:resolve');

            $arguments = [
                'paths' => [$image['filePath']],
                '--filter' => ['post_thumb'],
            ];

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
        }
    }
}

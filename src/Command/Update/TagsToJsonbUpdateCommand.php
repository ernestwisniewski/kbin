<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\Contracts\TagInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Repository\Contract\TagRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:tags:update',
    description: 'This command allows update tags columns.',
)]
class TagsToJsonbUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->update($this->entityManager->getRepository(Entry::class));
        $this->update($this->entityManager->getRepository(EntryComment::class));
        $this->update($this->entityManager->getRepository(Post::class));
        $this->update($this->entityManager->getRepository(PostComment::class));

        return Command::SUCCESS;
    }

    private function update(TagRepositoryInterface $repository)
    {
        /** @var TagInterface $entry */
        foreach ($repository->findWithTags() as $entry) {
            $entry->tagsTmp = $entry->getTags();
            $this->entityManager->persist($entry);
            echo $entry->getId().PHP_EOL;
        }

        $this->entityManager->flush();
    }
}

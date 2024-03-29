<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Kbin\Tag\TagExtract;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:tag:update',
    description: 'This command allows refresh entries tags.'
)]
class TagsUpdateCommand extends Command
{
    public function __construct(
        private readonly TagExtract $tagExtract,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $comments = $this->entityManager->getRepository(EntryComment::class)->findAll();
        foreach ($comments as $comment) {
            $comment->tags = ($this->tagExtract)($comment->body, $comment->magazine->name);
            $this->entityManager->persist($comment);
        }

        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        foreach ($posts as $post) {
            $post->tags = ($this->tagExtract)($post->body, $post->magazine->name);
            $this->entityManager->persist($post);
        }

        $comments = $this->entityManager->getRepository(PostComment::class)->findAll();
        foreach ($comments as $comment) {
            $comment->tags = ($this->tagExtract)($comment->body, $comment->magazine->name);
            $this->entityManager->persist($comment);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

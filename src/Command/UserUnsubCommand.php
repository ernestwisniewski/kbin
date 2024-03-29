<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command;

use App\Kbin\User\UserUnfollow;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'kbin:user:unsub')]
class UserUnsubCommand extends Command
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly UserUnfollow $userUnfollow
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->repository->findOneByUsername($input->getArgument('username'));

        if ($user) {
            foreach ($user->followers as $follower) {
                ($this->userUnfollow)($follower->follower, $user);
            }

            $io->success('User unsubscribed');

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}

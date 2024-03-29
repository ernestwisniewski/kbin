<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command;

use App\Kbin\Magazine\MagazineUnsubscribe;
use App\Repository\MagazineRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'kbin:magazine:unsub')]
class MagazineUnsubCommand extends Command
{
    public function __construct(
        private readonly MagazineRepository $repository,
        private readonly MagazineUnsubscribe $magazineUnsubscribe
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('magazine', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $magazine = $this->repository->findOneByName($input->getArgument('magazine'));

        if ($magazine) {
            foreach ($magazine->subscriptions as $sub) {
                ($this->magazineUnsubscribe)($magazine, $sub->user);
            }

            $io->success('User unsubscribed');

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}

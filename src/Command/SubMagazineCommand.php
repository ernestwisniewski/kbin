<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command;

use App\Kbin\Magazine\MagazineSubscribe;
use App\Kbin\Magazine\MagazineUnsubscribe;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'kbin:magazine:sub',
    description: 'This command allows subscribe magazine.',
)]
class SubMagazineCommand extends Command
{
    public function __construct(
        private readonly MagazineSubscribe $magazineSubscribe,
        private readonly MagazineUnsubscribe $magazineUnsubscribe,
        private readonly MagazineRepository $magazineRepository,
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('magazine', InputArgument::REQUIRED)
            ->addArgument('username', InputArgument::REQUIRED)
            ->addOption('unsub', 'u', InputOption::VALUE_NONE, 'Unsubscribe magazine.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $this->userRepository->findOneByUsername($input->getArgument('username'));
        $magazine = $this->magazineRepository->findOneByName($input->getArgument('magazine'));

        if (!$user) {
            $io->error('User not found.');

            return Command::FAILURE;
        }

        if (!$magazine) {
            $io->error('Magazine not found.');

            return Command::FAILURE;
        }

        if (!$input->getOption('unsub')) {
            ($this->magazineSubscribe)($magazine, $user);
        } else {
            ($this->magazineUnsubscribe)($magazine, $user);
        }

        return Command::SUCCESS;
    }
}

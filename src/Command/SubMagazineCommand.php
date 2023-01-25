<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\MagazineManager;
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
        private readonly MagazineManager $manager,
        private readonly MagazineRepository $magazineRepository,
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('magazine', InputArgument::REQUIRED)
            ->addArgument('username', InputArgument::REQUIRED)
            ->addOption('unsub', 'r', InputOption::VALUE_NONE, 'Unsubscribe magazine.');

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
            $this->manager->subscribe($magazine, $user);
        } else {
            $this->manager->unsubscribe($magazine, $user);
        }

        return Command::SUCCESS;
    }
}

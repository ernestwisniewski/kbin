<?php

namespace App\Command;

use App\Message\ActivityPub\UpdateActorMessage;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'kbin:actor:update',
    description: 'This command will allow you to update remote user info.',
)]
class ActorUpdateCommand extends Command
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly MagazineRepository $magazineRepository,
        private readonly MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('users', null, InputOption::VALUE_NONE)
            ->addOption('magazines', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userArg = $input->getArgument('user');

        if ($input->getOption('users')) {
            foreach ($this->repository->findRemoteForUpdate() as $u) {
                $this->bus->dispatch(new UpdateActorMessage($u->apProfileId));
                $io->info($u->username);
            }
        } elseif ($input->getOption('magazines')) {
            foreach ($this->magazineRepository->findRemoteForUpdate() as $u) {
                $this->bus->dispatch(new UpdateActorMessage($u->apProfileId));
                $io->info($u->name);
            }
        } elseif ($userArg) {
            $this->bus->dispatch(new UpdateActorMessage($userArg));
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Message\ActivityPub\UpdateActorMessage;
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
    public function __construct(private UserRepository $repository, private MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userArg = $input->getArgument('user');


        if ($input->getOption('all')) {
            foreach ($this->repository->findAllRemote() as $u) {
                $this->bus->dispatch(new UpdateActorMessage($u->apProfileId));
            }
        } elseif ($userArg) {
            $io->note(sprintf('You passed an user: %s', $userArg));
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }
}

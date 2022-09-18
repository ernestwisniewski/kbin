<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Command\Update\Async\NoteVisibilityMessage;
use App\Message\ActivityPub\UpdateActorMessage;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'kbin:ap:actor:update',
    description: 'This command allows refresh remote users.'
)]
class NoteVisibilityUpdateCommand extends Command
{
    public function __construct(
        private UserRepository $repository,
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->repository->findAllRemote() as $user) {
            $this->bus->dispatch(new UpdateActorMessage($user->apProfileId));
        }

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\ActivityPub\Inbox\ActivityMessage;
use App\Service\ActivityPub\ApHttpClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'kbin:ap:import',
    description: 'This command allows you import AP resource.'
)]
class ApImportObject extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ApHttpClient $client
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('url', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $body = $this->client->getActivityObject($input->getArgument('url'), false);

        $this->bus->dispatch(new ActivityMessage($body));

        return Command::SUCCESS;
    }
}

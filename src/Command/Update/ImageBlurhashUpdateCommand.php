<?php

declare(strict_types=1);

namespace App\Command\Update;

use App\Command\Update\Async\ImageBlurhashMessage;
use App\Repository\ImageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'kbin:blurhash:update',
    description: 'This command allows generate blurhash for images.',
)]
class ImageBlurhashUpdateCommand extends Command
{
    public function __construct(
        private readonly ImageRepository $repository,
        private readonly MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $images = $this->repository->findAll();

        foreach ($images as $image) {
            $this->bus->dispatch(new ImageBlurhashMessage($image->getId()));
        }

        return Command::SUCCESS;
    }
}

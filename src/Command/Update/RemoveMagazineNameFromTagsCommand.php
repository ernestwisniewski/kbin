<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command\Update;

use App\Repository\MagazineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:magazine:tags',
    description: 'This command allows remove magazine name from tags.'
)]
class RemoveMagazineNameFromTagsCommand extends Command
{
    public function __construct(
        private readonly MagazineRepository $magazineRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->magazineRepository->findAll() as $magazine) {
            if ($tags = $magazine->tags) {
                $magazine->tags = array_values(array_filter($tags, fn ($val) => $val !== $magazine->name));
                if (empty($magazine->tags)) {
                    $magazine->tags = null;
                }
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

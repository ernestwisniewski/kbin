<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command\Update;

use App\Repository\MagazineRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'kbin:update:magazines:ap_profile',
    description: 'This command allows generate Ap profile.',
)]
class LocalMagazineApProfile extends Command
{
    public function __construct(
        private readonly MagazineRepository $repository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $magazines = $this->repository->createQueryBuilder('m')
            ->where('m.apId IS NULL')
            ->getQuery()
            ->getResult();

        foreach ($magazines as $magazine) {
            $magazine->apProfileId = $this->urlGenerator->generate(
                'ap_magazine',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $io->info($magazine->name);
            $this->repository->save($magazine, true);
        }

        return Command::SUCCESS;
    }
}

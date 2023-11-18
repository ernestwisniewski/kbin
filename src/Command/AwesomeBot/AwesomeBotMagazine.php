<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command\AwesomeBot;

use App\Entity\Magazine;
use App\Kbin\Entry\Badge\EntryBadgeCreate;
use App\Kbin\Entry\DTO\EntryBadgeDto;
use App\Kbin\Magazine\DTO\MagazineDto;
use App\Kbin\Magazine\MagazineCreate;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(name: 'kbin:awesome-bot:magazine:create')]
class AwesomeBotMagazine extends Command
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly MagazineCreate $magazineCreate,
        private readonly EntryBadgeCreate $entryBadgeCreate
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command allows you to create awesome-bot magazine.')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('magazine_name', InputArgument::REQUIRED)
            ->addArgument('magazine_title', InputArgument::REQUIRED)
            ->addArgument('url', InputArgument::REQUIRED)
            ->addArgument('tags', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $this->repository->findOneByUsername($input->getArgument('username'));

        if (!$user) {
            $io->error('User doesn\'t exist.');

            return Command::FAILURE;
        }

        try {
            $dto = new MagazineDto();
            $dto->name = $input->getArgument('magazine_name');
            $dto->title = $input->getArgument('magazine_title');
            $dto->description = 'Powered by '.$input->getArgument('url');
            $dto->setOwner($user);

            $magazine = ($this->magazineCreate)($dto, $user);

            $this->createBadges(
                $magazine,
                $input->getArgument('url'),
                $input->getArgument('tags') ? explode(',', $input->getArgument('tags')) : []
            );
        } catch (\Exception $e) {
            $io->error('Can\'t create magazine');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    #[Pure]
    private function createBadges(Magazine $magazine, string $url, array $tags): Collection
    {
        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', $url);

        $content = $crawler->filter('.markdown-body')->first()->children();

        $labels = [];
        foreach ($content as $elem) {
            if (\in_array($elem->nodeName, $tags)) {
                $labels[] = $elem->nodeValue;
            }
        }

        $badges = [];
        foreach ($labels as $label) {
            ($this->entryBadgeCreate)(EntryBadgeDto::create($magazine, $label));
        }

        return new ArrayCollection($badges);
    }
}

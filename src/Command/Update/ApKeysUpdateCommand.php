<?php

declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\Contracts\ActivityPubActorInterface;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\KeysGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:ap:keys:update',
    description: 'This command allows generate keys for AP Actors.',
)]
class ApKeysUpdateCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generate($this->userRepository->findWithoutKeys());
        $this->generate($this->magazineRepository->findWithoutKeys());

        return Command::SUCCESS;
    }

    private function generate(array $actors)
    {
        /**
         * @var $actor ActivityPubActorInterface
         */
        foreach ($actors as $actor) {
            $actor = KeysGenerator::generate($actor);
            $this->entityManager->persist($actor);
        }

        $this->entityManager->flush();
    }
}

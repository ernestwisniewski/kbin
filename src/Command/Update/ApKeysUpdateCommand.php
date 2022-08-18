<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\Contracts\ActivityPubActorInterface;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\ActivityPub\KeysGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApKeysUpdateCommand extends Command
{
    protected static $defaultName = 'kbin:ap:keys:update';

    public function __construct(
        private UserRepository $userRepository,
        private MagazineRepository $magazineRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows generate keys for AP Actors.');
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

<?php

namespace App\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Service\UserManager;
use App\DTO\RegisterUserDto;

class UserCommand extends Command
{
    protected static $defaultName = 'kbin:user';
    protected static string $defaultDescription = 'This command allows you to create or remove user account.';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $repository,
        private UserManager $manager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $remove = $input->getOption('remove');
        $user   = $this->repository->findOneByUsername($input->getArgument('username'));

        if ($user && !$remove) {
            $io->error('User exists.');

            return Command::FAILURE;
        }

        if ($user) {
            // @todo publish delete user message
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $io->success('The user deletion process has started.');

            return Command::SUCCESS;
        }

        $this->createUser($input, $io);

        return Command::SUCCESS;
    }

    private function createUser(InputInterface $input, SymfonyStyle $io): void
    {
        $user = $this->manager->create(
            (new RegisterUserDto())->create(
                $input->getArgument('username'),
                $input->getArgument('email'),
                $input->getArgument('password'),
            ),
            false
        );

        $user->isVerified = true;
        $this->entityManager->flush();

        $io->success('A user has been created. It is recommended to change the password after the first login.');
    }
}

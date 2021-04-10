<?php

namespace App\Command;

use App\DTO\RegisterUserDto;
use App\DTO\UserDto;
use App\Repository\UserRepository;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserCommand extends Command
{
    protected static $defaultName = 'kbin:user';
    protected static string $defaultDescription = 'This command allows you to create or remove user account.';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserManager $userManager
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
        $user   = $this->userRepository->findOneByUsername($input->getArgument('username'));

        if ($user) {
            // publish delete user command
            $io->success('The user deletion process has started.');

            return 1;
        }

        $user = $this->userManager->create(
            (new RegisterUserDto())->create(
                $input->getArgument('username'),
                $input->getArgument('email'),
                $input->getArgument('password'),
            )
        );

        $user->isVerified = true;
        $this->entityManager->flush();

        $io->success('A user has been created. It is recommended to change the password after the first login.');

        return Command::SUCCESS;
    }
}

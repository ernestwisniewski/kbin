<?php declare(strict_types = 1);

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AdminCommand extends Command
{
    protected static $defaultName = 'kbin:user:admin';

    public function __construct(private EntityManagerInterface $entityManager, private UserRepository $repository)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows you to grant administrator privileges to the user.')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove privileges');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $remove = $input->getOption('remove');
        $user   = $this->repository->findOneByUsername($input->getArgument('username'));

        if (!$user) {
            $io->error('User not found.');

            return Command::FAILURE;
        }

        $user->setOrRemoveAdminRole($remove);
        $this->entityManager->flush();

        $remove ? $io->success('Administrator privileges have been revoked.')
            : $io->success('Administrator privileges has been granted.');

        return Command::SUCCESS;
    }
}

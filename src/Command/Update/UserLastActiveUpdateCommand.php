<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Service\TagManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:users:lastActive:update',
    description: 'This command allows set user last active date.'
)]
class UserLastActiveUpdateCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->entityManager->getRepository(User::class);

        foreach ($repo->findAll() as $user) {
            $activity = $repo->findPublicActivity(1, $user);
            if($activity->count()) {
                $user->lastActive = $activity->getCurrentPageResults()[0]->lastActive;
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

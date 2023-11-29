<?php

declare(strict_types=1);

namespace App\Command\Cron;

use App\Entity\Image;
use App\Kbin\Post\PostImageDetach;
use App\Kbin\User\UserAvatarDetach;
use App\Kbin\User\UserCoverDetach;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:images:delete-old',
    description: 'This command allows you to delete images from old federated content.'
)]
class ImageProcessingCommand extends Command
{
    private int $batchSize = 25;
    private int $monthsAgo = 3;
    private bool $all = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PostImageDetach $postImageDetach,
        private readonly UserCoverDetach $userCoverDetach,
        private readonly UserAvatarDetach $userAvatarDetach
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'Type of images to delete (posts, users).')
            ->addArgument('monthsAgo', InputArgument::REQUIRED, 'Delete images older than x months.')
            ->addOption('all', null, InputOption::VALUE_OPTIONAL, 'Delete images from all posts, including those that have recorded activity (comments, upvotes, boosts).')
            ->addOption('batchSize', null, InputOption::VALUE_OPTIONAL, 'Number of images to delete at a time.');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $this->monthsAgo = (int)$input->getArgument('monthsAgo');
        $this->all = (bool)$input->getOption('all');
        if ($input->getOption('batchSize')) {
            $this->batchSize = (int)$input->getOption('batchSize');
        }

        if ($type === 'posts') {
            $this->deletePostsImages();
        }

        if ($type === 'users') {
            $this->deleteUsersImages();
        }

        $this->entityManager->clear();

        return Command::SUCCESS;
    }

    private function deletePostsImages(): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $timeAgo = new \DateTime("-{$this->monthsAgo} months");

        $query = $queryBuilder
            ->select('p')
            ->from('App\Entity\Post', 'p')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->lt('p.createdAt', ':timeAgo'),
                    $queryBuilder->expr()->neq('i.id', 1),
                    $queryBuilder->expr()->isNotNull('p.apId'),
                    $this->all ? null : $queryBuilder->expr()->eq('p.upVotes', 0),
                    $this->all ? null : $queryBuilder->expr()->eq('p.commentCount', 0),
                    $this->all ? null : $queryBuilder->expr()->isNull('p.tags'),
                    $this->all ? null : $queryBuilder->expr()->eq('p.favouriteCount', 0),
                    $this->all ? null : $queryBuilder->expr()->isNotNull('p.image')
                )
            )
            ->leftJoin('p.image', 'i')
            ->orderBy('p.id', 'ASC')
            ->setParameter('timeAgo', $timeAgo)
            ->setMaxResults($this->batchSize)
            ->getQuery();

        $posts = $query->getResult();

        $placeholder = $this->entityManager->getRepository(Image::class)->find(1);

        foreach ($posts as $post) {
            ($this->postImageDetach)($post);
            $post->image = $placeholder;
            $this->entityManager->flush();
        }
    }

    private function deleteUsersImages()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $timeAgo = new \DateTime("-{$this->monthsAgo} months");

        $query = $queryBuilder
            ->select('u')
            ->from('App\Entity\User', 'u')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->isNotNull('u.avatar'),
                        $queryBuilder->expr()->isNotNull('u.cover')
                    ),
                    $queryBuilder->expr()->lt('u.apFetchedAt', ':timeAgo'),
                    $queryBuilder->expr()->isNotNull('u.apId')
                )
            )
            ->orderBy('u.apFetchedAt', 'ASC')
            ->setParameter('timeAgo', $timeAgo)
            ->setMaxResults($this->batchSize)
            ->getQuery();

        $users = $query->getResult();

        foreach ($users as $user) {
            ($this->userAvatarDetach)($user);
            ($this->userCoverDetach)($user);
        }
    }
}

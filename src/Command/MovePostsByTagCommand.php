<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use App\Repository\PostRepository;
use App\Service\PostManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'kbin:posts:move',
    description: 'This command will allow you to move the posts to the new magazine based on the tag.'
)]
class MovePostsByTagCommand extends Command
{
    public function __construct(
        private readonly PostManager $postManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly MagazineRepository $magazineRepository,
        private readonly PostRepository $postRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('magazine', InputArgument::REQUIRED)
            ->addArgument('tag', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $magazine = $this->magazineRepository->findOneByName($input->getArgument('magazine'));
        $tag = $input->getArgument('tag');

        if (!$magazine) {
            $io->error('The magazine does not exist.');

            return Command::FAILURE;
        }

        $qb = $this->postRepository->createQueryBuilder('p');

        $qb->andWhere("JSONB_CONTAINS(p.tags, '\"".$tag."\"') = true");

        $posts = $qb->getQuery()->getResult();

        foreach ($posts as $post) {
            $output->writeln((string)$post->getId());
            $this->postManager->changeMagazine($post, $magazine);
        }

        return Command::SUCCESS;
    }

    private function moveComments(ArrayCollection|Collection $comments, Magazine $magazine)
    {
        foreach ($comments as $comment) {
            /*
             * @var EntryComment $comment
             */
            $comment->magazine = $magazine;

            $this->moveReports($comment->reports, $magazine);
            $this->moveFavourites($comment->favourites, $magazine);

            $this->entityManager->persist($comment);
        }
    }

    private function moveReports(ArrayCollection|Collection $reports, Magazine $magazine)
    {
        foreach ($reports as $report) {
            /*
             * @var Report $report
             */
            $report->magazine = $magazine;

            $this->entityManager->persist($report);
        }
    }

    private function moveFavourites(ArrayCollection|Collection $favourites, Magazine $magazine)
    {
        foreach ($favourites as $favourite) {
            /*
             * @var Favourite $favourite
             */
            $favourite->magazine = $magazine;

            $this->entityManager->persist($favourite);
        }
    }
}

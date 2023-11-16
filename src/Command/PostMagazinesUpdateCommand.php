<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Post;
use App\Kbin\Post\PostChangeMagazine;
use App\Repository\MagazineRepository;
use App\Repository\PostRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:posts:magazines',
    description: 'This command allows assing post to magazine.'
)]
class PostMagazinesUpdateCommand extends Command
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly PostChangeMagazine $postChangeMagazine,
        private readonly MagazineRepository $magazineRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $posts = $this->postRepository->findTaggedFederatedInRandomMagazine();
        foreach ($posts as $post) {
            $this->handleMagazine($post, $output);
        }

        return Command::SUCCESS;
    }

    private function handleMagazine(Post $post, OutputInterface $output): void
    {
        if (!$post->tags) {
            return;
        }

        $output->writeln((string) $post->getId());
        foreach ($post->tags as $tag) {
            if ($magazine = $this->magazineRepository->findOneByName($tag)) {
                $output->writeln($magazine->name);
                ($this->postChangeMagazine)($post, $magazine);
                break;
            }

            if ($magazine = $this->magazineRepository->findByTag($tag)) {
                $output->writeln($magazine->name);
                ($this->postChangeMagazine)($post, $magazine);
                break;
            }
        }
    }
}

<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\Post;
use App\Repository\MagazineRepository;
use App\Repository\PostRepository;
use App\Service\PostManager;
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
        private PostRepository $postRepository,
        private PostManager $postManager,
        private MagazineRepository $magazineRepository
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
                $this->postManager->changeMagazine($post, $magazine);
                break;
            }

            if ($magazines = $this->magazineRepository->findByTag($tag)) {
                $output->writeln($magazines[0]->name);
                $this->postManager->changeMagazine($post, $magazines[array_rand($magazines)]);
                break;
            }
        }
    }
}

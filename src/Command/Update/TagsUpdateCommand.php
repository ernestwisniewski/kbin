<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\TagManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagsUpdateCommand extends Command
{
    protected static $defaultName = 'kbin:update:tag';

    public function __construct(private TagManager $tagManager, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows refresh entries tags.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $comments = $this->entityManager->getRepository(EntryComment::class)->findAll();
        foreach ($comments as $comment) {
            $comment->tags = $this->tagManager->extract($comment->body);
            $this->entityManager->persist($comment);
        }

        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        foreach ($posts as $post) {
            $post->tags = $this->tagManager->extract($post->body);
            $this->entityManager->persist($post);
        }

        $comments = $this->entityManager->getRepository(PostComment::class)->findAll();
        foreach ($comments as $comment) {
            $comment->tags = $this->tagManager->extract($comment->body);
            $this->entityManager->persist($comment);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

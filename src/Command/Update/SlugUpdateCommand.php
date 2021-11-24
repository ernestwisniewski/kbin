<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\Entry;
use App\Entity\Post;
use App\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SlugUpdateCommand extends Command
{
    protected static $defaultName = 'kbin:update:slug';

    public function __construct(private Slugger $slugger, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows refresh entries slugs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entries = $this->entityManager->getRepository(Entry::class)->findAll();
        foreach ($entries as $entry) {
            $entry->slug = $this->slugger->slug($entry->title);
            $this->entityManager->persist($entry);
        }

        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        foreach ($posts as $post) {
            $post->slug = $this->slugger->slug($post->body);
            $this->entityManager->persist($post);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

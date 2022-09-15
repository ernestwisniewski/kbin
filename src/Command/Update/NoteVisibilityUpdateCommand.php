<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Command\Update\Async\NoteVisibilityMessage;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'kbin:note:visibility',
    description: 'This command allows refresh notes visibility.'
)]
class NoteVisibilityUpdateCommand extends Command
{
    public function __construct(
        private PostRepository $postRepository,
        private PostCommentRepository $postCommentRepository,
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $posts = $this->postRepository->findFederated();
        foreach ($posts as $post) {
            $this->bus->dispatch(new NoteVisibilityMessage($post->getId(), get_class($post)));
        }

        $comments = $this->postCommentRepository->findFederated();
        foreach ($comments as $comment) {
            $this->bus->dispatch(new NoteVisibilityMessage($comment->getId(), get_class($comment)));
        }

        return Command::SUCCESS;
    }
}

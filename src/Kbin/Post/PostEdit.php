<?php

declare(strict_types=1);

namespace App\Kbin\Post;

use App\DTO\PostDto;
use App\Entity\Post;
use App\Event\Post\PostEditedEvent;
use App\Message\DeleteImageMessage;
use App\Repository\ImageRepository;
use App\Service\MentionManager;
use App\Service\TagManager;
use App\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

readonly class PostEdit
{
    public function __construct(
        private Slugger $slugger,
        private MentionManager $mentionManager,
        private TagManager $tagManager,
        private ImageRepository $imageRepository,
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post, PostDto $dto): Post
    {
        Assert::same($post->magazine->getId(), $dto->magazine->getId());

        $post->body = $dto->body;
        $post->lang = $dto->lang;
        $post->isAdult = $dto->isAdult || $post->magazine->isAdult;
        $post->slug = $this->slugger->slug($dto->body ?? $dto->magazine->name.' '.$dto->image->altText);
        $oldImage = $post->image;
        if ($dto->image && $dto->image->id !== $post->image->getId()) {
            $post->image = $this->imageRepository->find($dto->image->id);
        }
        $post->tags = $dto->body ? $this->tagManager->extract($dto->body, $post->magazine->name) : null;
        $post->mentions = $dto->body ? $this->mentionManager->extract($dto->body) : null;
        $post->visibility = $dto->getVisibility();
        $post->editedAt = new \DateTimeImmutable('@'.time());
        if (empty($post->body) && null === $post->image) {
            throw new \Exception('Post body and image cannot be empty');
        }

        $this->entityManager->flush();

        if ($oldImage && $post->image !== $oldImage) {
            $this->messageBus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->eventDispatcher->dispatch(new PostEditedEvent($post));

        return $post;
    }
}

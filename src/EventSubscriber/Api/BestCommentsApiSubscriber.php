<?php declare(strict_types = 1);

namespace App\EventSubscriber\Api;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\ApiDataProvider\DtoPaginator;
use App\DTO\PostDto;
use App\Factory\ImageFactory;
use App\Factory\PostCommentFactory;
use App\Factory\UserFactory;
use App\Repository\PostRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class BestCommentsApiSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PostRepository $repository,
        private PostCommentFactory $commentFactory,
        private UserFactory $userFactory,
        private ImageFactory $imageFactory
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['transform', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function transform(ViewEvent $event): void
    {
        if (!$event->getControllerResult() instanceof DtoPaginator) {
            return;
        }

        if (!iterator_count($event->getControllerResult())) {
            return;
        }

        if (!iterator_to_array($event->getControllerResult()->getIterator())[0] instanceof PostDto) {
            return;
        }

        foreach ($event->getControllerResult() as $post) {
            $comments       = $this->repository->find($post->getId())->getBestComments();
            $commentFactory = $this->commentFactory;
            $userFactory    = $this->userFactory;
            $imageFactory   = $this->imageFactory;

            $comments = $comments->map(function ($val) use ($commentFactory, $userFactory, $imageFactory, $post) {
                $val               = $commentFactory->createDto($val);
                $val->user         = $userFactory->createDto($val->user);
                $val->user->avatar = $val->user->avatar ? $imageFactory->createDto($val->user->avatar) : null;

                return $val;
            });

            $post->bestComments = $comments;
        }
    }
}

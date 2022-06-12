<?php declare(strict_types=1);

namespace App\EventSubscriber\Api;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Factory\ImageFactory;
use App\Factory\PostCommentFactory;
use App\Factory\UserFactory;
use App\Repository\PostRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

// @todo temporary fix
final class BestCommentsFixApiSubscriber implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['transform', EventPriorities::POST_SERIALIZE],
        ];
    }

    public function transform(ViewEvent $event): void
    {
        if ($event->getRequest()->getPathInfo() !== '/api/posts') {
            return;
        }

        $result = $event->getControllerResult();

        $results = json_decode($result, true);
        foreach ($results['hydra:member'] as $index => $member) {
            $results['hydra:member'][$index]['bestComments'] = array_values($results['hydra:member'][$index]['bestComments']);
        }

        $event->setControllerResult(json_encode($results));
    }
}

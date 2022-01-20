<?php declare(strict_types=1);

namespace App\EventListener;

use App\Service\TagManager;
use FOS\ElasticaBundle\Event\PostTransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ElasticaTagsListener implements EventSubscriberInterface
{
    public function __construct(private TagManager $manager)
    {
    }

    public function addTags(PostTransformEvent $event)
    {
        $document = $event->getDocument();

        if (empty($document->getData()['body'])) {
            return;
        }

        $document->set('tags', $this->manager->extract($document->getData()['body']));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostTransformEvent::class => 'addTags',
        ];
    }
}

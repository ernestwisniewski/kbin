<?php declare(strict_types=1);

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormEvents;
use App\Repository\ImageRepository;

final class UserAvatarListener implements EventSubscriberInterface
{
    /**
     * @var ImageRepository
     */
    private $images;

    public function __construct(ImageRepository $images)
    {
        $this->images = $images;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => ['onPostSubmit', -200],
        ];
    }

    public function onPostSubmit(PostSubmitEvent $event): void
    {
        if (!$event->getForm()->isValid()) {
            return;
        }

        $data = $event->getData();

        if (!$event->getForm()->has('avatar')) {
            return;
        }

        $upload = $event->getForm()->get('avatar')->getData();

        if ($upload && !$data->getAvatar()) {
            $image = $this->images->findOrCreateFromUpload($upload);

            $data->setAvatar($image);
        }
    }
}

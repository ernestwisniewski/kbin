<?php declare(strict_types=1);

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormEvents;
use App\Repository\ImageRepository;

final class ImageListener implements EventSubscriberInterface
{
    /**
     * @var ImageRepository
     */
    private $images;
    private string $fieldName;

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

        $fieldName = $this->fieldName ?? 'image';

        if (!$event->getForm()->has($fieldName)) {
            return;
        }

        $upload = $event->getForm()->get($fieldName)->getData();

        $getter = 'get'.ucwords($fieldName);
        if ($upload && !$data->$getter()) {
            $image = $this->images->findOrCreateFromUpload($upload);

            $setter = 'set'.ucwords($fieldName);
            $data->$setter($image);
        }
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }
}

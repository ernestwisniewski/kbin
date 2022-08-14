<?php declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\DTO\EntryDto;
use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Repository\MagazineRepository;
use App\Service\ActivityPubManager;
use App\Service\EntryManager;
use DateTime;
use DateTimeImmutable;

class Page
{
    public function __construct(
        private MarkdownConverter $markdownConverter,
        private MagazineRepository $magazineRepository,
        private EntryManager $entryManager,
        private ActivityPubManager $activityPubManager,
    ) {
    }

    public function create(array $object): ActivityPubActivityInterface
    {
        $dto           = new EntryDto();
        $dto->body     = $object['content'] ? $this->markdownConverter->convert($object['content']) : null;
        $dto->magazine = $this->magazineRepository->findOneByName('random'); // @todo magazine by tags
        $dto->title    = $object['name'];
        $dto->apId     = $object['id'];

        if (isset($object['attachment']) || isset($object['image'])) {
            $dto->image = $this->activityPubManager->handleImages($object['attachment']);
        }

        $this->handleUrl($dto, $object);
        $this->handleDate($dto, $object['published']);

        return $this->entryManager->create($dto, $this->activityPubManager->findActorOrCreate($object['attributedTo']), false);
    }

    private function handleDate(EntryDto $dto, string $date): void
    {
        $dto->createdAt  = new DateTimeImmutable($date);
        $dto->lastActive = new DateTime($date);
    }

    private function handleUrl(EntryDto $dto, ?array $object): void
    {
        $attachment = $object['attachment'];

        try {
            if (is_array($attachment)) {
                $link = array_filter(
                    $attachment,
                    fn($val) => in_array($val['type'], ['Link'])
                );

                $dto->url = $link[0]['href'];
            }
        } catch (\Exception $e) {
        }

        if (!$dto->url && isset($object['url'])) {
            $dto->url = $object['url'];
        }
    }
}

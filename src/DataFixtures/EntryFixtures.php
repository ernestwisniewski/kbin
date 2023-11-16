<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\EntryDto;
use App\Kbin\Entry\EntryCreate;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class EntryFixtures extends BaseFixture implements DependentFixtureInterface
{
    public const ENTRIES_COUNT = MagazineFixtures::MAGAZINES_COUNT * 15;

    public function __construct(
        private readonly EntryCreate $entryCreate,
        private readonly ImageManager $imageManager,
        private readonly ImageRepository $imageRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function getDependencies(): array
    {
        return [
            MagazineFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager): void
    {
        foreach ($this->provideRandomEntries(self::ENTRIES_COUNT) as $index => $entry) {
            $dto = new EntryDto();
            $dto->magazine = $entry['magazine'];
            $dto->user = $entry['user'];
            $dto->title = $entry['title'];
            $dto->url = $entry['url'];
            $dto->body = $entry['body'];
            $dto->ip = $entry['ip'];
            $dto->lang = 'en';

            $entity = ($this->entryCreate)($dto, $entry['user']);

            $roll = rand(1, 400);
            if ($roll % 5) {
                try {
                    $tempFile = $this->imageManager->download("https://picsum.photos/300/$roll?hash=$roll");
                } catch (\Exception $e) {
                    $tempFile = null;
                }

                if ($tempFile) {
                    $image = $this->imageRepository->findOrCreateFromPath($tempFile);

                    $entity->image = $image;
                    $this->entityManager->flush();
                }
            }

            $entity->createdAt = $this->getRandomTime();

            $entity->updateCounts();
            $entity->updateLastActive();
            $entity->updateRanking();

            $this->addReference('entry_'.$index, $entity);
        }

        $manager->flush();
    }

    private function provideRandomEntries($count = 1): iterable
    {
        for ($i = 0; $i <= $count; ++$i) {
            $isUrl = $this->faker->numberBetween(0, 1);
            $body = $isUrl ? null : $this->faker->paragraphs($this->faker->numberBetween(1, 10), true);

            yield [
                'title' => $this->faker->realText($this->faker->numberBetween(10, 255)),
                'url' => $isUrl ? $this->faker->url : null,
                'body' => $body,
                'magazine' => $this->getReference('magazine_'.rand(1, (int) MagazineFixtures::MAGAZINES_COUNT)),
                'user' => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                'ip' => $this->faker->ipv4,
            ];
        }
    }
}

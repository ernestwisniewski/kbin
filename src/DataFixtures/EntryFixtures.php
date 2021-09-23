<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\EntryDto;
use App\Repository\ImageRepository;
use App\Service\EntryManager;
use App\Service\ImageManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class EntryFixtures extends BaseFixture implements DependentFixtureInterface
{
    const ENTRIES_COUNT = MagazineFixtures::MAGAZINES_COUNT * 15;

    public function __construct(
        private EntryManager $entryManager,
        private ImageManager $imageManager,
        private ImageRepository $imageRepository,
        private EntityManagerInterface $entityManager
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
            $dto = (new EntryDto())->create(
                $entry['magazine'],
                $entry['user'],
                $entry['title'],
                $entry['url'],
                $entry['body'],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $entry['ip']
            );

            $entity = $this->entryManager->create($dto, $entry['user']);

            $roll = rand(1, 400);
            if ($roll % 5) {
                $entity = $this->entryManager->create($dto, $entry['user']);

                $tempFile = $this->imageManager->download("https://picsum.photos/300/$roll?hash=$roll");
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

            $this->addReference('entry'.'_'.$index, $entity);
        }

        $manager->flush();
    }

    private function provideRandomEntries($count = 1): iterable
    {
        for ($i = 0; $i <= $count; $i++) {
            $isUrl = $this->faker->numberBetween(0, 1);
            $body  = $isUrl ? null : $this->faker->paragraphs($this->faker->numberBetween(1, 10), true);

            yield [
                'title'    => $this->faker->realText($this->faker->numberBetween(10, 255)),
                'url'      => $isUrl ? $this->faker->url : null,
                'body'     => $body,
                'magazine' => $this->getReference('magazine_'.rand(1, intval(MagazineFixtures::MAGAZINES_COUNT))),
                'user'     => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                'ip'       => $this->faker->ipv4,
            ];
        }
    }
}

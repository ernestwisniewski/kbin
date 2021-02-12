<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use App\Service\EntryManager;
use App\DTO\EntryDto;

class EntryFixtures extends BaseFixture implements DependentFixtureInterface
{
    const ENTRIES_COUNT = MagazineFixtures::MAGAZINES_COUNT * 15;

    private EntryManager $entryManager;
    private ImageManager $imageManager;
    private ImageRepository $imageRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        EntryManager $entryManager,
        ImageManager $imageManager,
        ImageRepository $imageRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->entryManager    = $entryManager;
        $this->imageManager    = $imageManager;
        $this->imageRepository = $imageRepository;
        $this->entityManager   = $entityManager;
    }

    public function getDependencies()
    {
        return [
            MagazineFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        foreach ($this->provideRandomEntries(self::ENTRIES_COUNT) as $index => $entry) {
            $dto = (new EntryDto())->create($entry['magazine'], $entry['title'], $entry['url'], $entry['body']);

            $entity = $this->entryManager->create($dto, $entry['user']);

            $roll = rand(100, 500);
            if ($roll % 5) {

                $tempFile = $this->imageManager->download("https://picsum.photos/300/$roll?hash=$roll");
                $image    = $this->imageRepository->findOrCreateFromPath($tempFile);

                $entity->setImage($image);
                $this->entityManager->flush();
            }

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
            ];
        }
    }
}

<?php declare(strict_types = 1);

namespace App\DataFixtures;

use App\DTO\MagazineDto;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use App\Service\MagazineManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class MagazineFixtures extends BaseFixture implements DependentFixtureInterface
{
    const MAGAZINES_COUNT = UserFixtures::USERS_COUNT / 3;

    public function __construct(
        private MagazineManager $magazineManager,
        private ImageManager $imageManager,
        private ImageRepository $imageRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function loadData(ObjectManager $manager): void
    {
        foreach ($this->provideRandomMagazines(self::MAGAZINES_COUNT) as $index => $magazine) {
            $image    = null;
            $width    = rand(100, 400);
            $tempFile = $this->imageManager->download("https://picsum.photos/{$width}/?hash=$width");
            if ($tempFile) {
                $image = $this->imageRepository->findOrCreateFromPath($tempFile);
                $this->entityManager->flush();
            }

            $dto = (new MagazineDto())->create(
                $magazine['name'],
                $magazine['title'],
                $magazine['badges'],
                $magazine['description'],
                $magazine['rules'],
                null,
                $image
            );

            $entity = $this->magazineManager->create($dto, $magazine['user']);

            $this->addReference('magazine'.'_'.$index, $entity);
        }

        $manager->flush();
    }

    private function provideRandomMagazines($count = 1): iterable
    {
        $titles = [];
        for ($i = 0; $i <= $count; $i++) {
            $title = substr($this->faker->words($this->faker->numberBetween(1, 5), true), 0, 50);

            if (in_array($title, $titles)) {
                continue;
            }

            $titles[] = $title;

            yield [
                'name'        => substr($this->camelCase($title), 0, 24),
                'title'       => $title,
                'user'        => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                'description' => rand(0, 3) ? null : $this->faker->realText($this->faker->numberBetween(10, 550)),
                'rules'       => rand(0, 3) ? null : $this->faker->realText($this->faker->numberBetween(10, 550)),
                'badges'      => new ArrayCollection(),
            ];
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}

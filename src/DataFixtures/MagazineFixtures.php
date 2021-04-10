<?php declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Service\MagazineManager;
use App\DTO\MagazineDto;

class MagazineFixtures extends BaseFixture implements DependentFixtureInterface
{
    const MAGAZINES_COUNT = UserFixtures::USERS_COUNT / 3;

    public function __construct(private MagazineManager $magazineManager)
    {
    }

    public function loadData(ObjectManager $manager): void
    {
        foreach ($this->provideRandomMagazines(self::MAGAZINES_COUNT) as $index => $magazine) {
            $dto = (new MagazineDto())->create($magazine['name'], $magazine['title'], $magazine['description'], $magazine['rules']);

            $entity = $this->magazineManager->create($dto, $magazine['user']);

            $this->addReference('magazine'.'_'.$index, $entity);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
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
            ];
        }
    }
}

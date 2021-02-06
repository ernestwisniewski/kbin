<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Service\MagazineManager;
use App\DTO\MagazineDto;

class MagazineFixtures extends BaseFixture implements DependentFixtureInterface
{
    const MAGAZINES_COUNT = UserFixtures::USERS_COUNT / 5;

    private MagazineManager $magazineManager;

    public function __construct(MagazineManager $magazineManager)
    {
        $this->magazineManager = $magazineManager;
    }

    public function loadData(ObjectManager $manager)
    {
        foreach ($this->provideRandomMagazines(self::MAGAZINES_COUNT) as $index => $magazine) {

            $dto = (new MagazineDto())->create($magazine['name'], $magazine['title']);

            $entity = $this->magazineManager->create($dto, $magazine['user']);

            $this->addReference('magazine'.'_'.$index, $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }

    private function provideRandomMagazines($count = 1): iterable
    {
        $titles = [];
        for ($i = 0; $i <= $count; $i++) {
            $title = $this->faker->words($this->faker->numberBetween(1, 5), true);

            if (in_array($title, $titles)) {
                continue;
            }

            $titles[] = $title;

            yield [
                'name'  => $this->camelCase($title),
                'title' => $title,
                'user'  => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
            ];
        }
    }
}

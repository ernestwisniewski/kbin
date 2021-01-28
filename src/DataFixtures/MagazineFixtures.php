<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\MagazineDto;
use App\Service\MagazineManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Magazine;

class MagazineFixtures extends BaseFixture implements DependentFixtureInterface
{
    const MAGAZINES_COUNT = 30;

    private MagazineManager $magazineManager;

    public function __construct(MagazineManager $magazineManager)
    {
        $this->magazineManager = $magazineManager;
    }

    public function loadData(ObjectManager $manager)
    {
        foreach ($this->provideRandomMagazines(self::MAGAZINES_COUNT) as $index => $magazine) {

            $dto = (new MagazineDto())->create($magazine['name'], $magazine['title']);

            $entity = $this->magazineManager->createMagazine($dto, $magazine['user']);

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
        for ($i = 0; $i <= $count; $i++) {
            $title = $this->faker->words($this->faker->numberBetween(1, 5), true);

            yield [
                'name'  => $this->camelCase($title),
                'title' => $title,
                'user'  => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
            ];
        }
    }
}

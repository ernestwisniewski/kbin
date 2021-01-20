<?php declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Magazine;

class MagazineFixtures extends BaseFixture implements DependentFixtureInterface
{
    const MAGAZINES_COUNT = 15;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function loadData(ObjectManager $manager)
    {
        foreach ($this->provideRandomMagazines(self::MAGAZINES_COUNT) as $index => $magazine) {
            $newMagazine = new Magazine(
                $magazine['name'],
                $magazine['title'],
                $magazine['user']
            );

            $manager->persist($newMagazine);

            $this->addReference('magazine'.'_'.$index, $newMagazine);
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
            $names = [$this->faker->word, $this->getCamlcaseWords()];
            $rand  = array_rand($names);

            yield [
                'name'  => $names[$rand],
                'title' => $this->faker->words(3, true),
                'user'  => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
            ];
        }
    }

    private function getCamlcaseWords(): string
    {
        $words = $this->faker->words(2);

        return $words[0].ucfirst($words[1]);
    }
}

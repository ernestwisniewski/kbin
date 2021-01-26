<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Entry;

class EntryFixtures extends BaseFixture implements DependentFixtureInterface
{
    const ENTRIES_COUNT = 50;

    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
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
            $newEntry = new Entry(
                $entry['title'],
                $entry['url'],
                $entry['body'],
                $entry['magazine'],
                $entry['user']
            );

            $manager->persist($newEntry);

            $this->addReference('entry'.'_'.$index, $newEntry);
        }

        $manager->flush();
    }

    private function provideRandomEntries($count = 1): iterable
    {
        for ($i = 0; $i <= $count; $i++) {
            $isUrl = $this->faker->numberBetween(0, 1);
            $body  = $isUrl ? null : $this->faker->paragraphs($this->faker->numberBetween(1, 10), true);

            yield [
                'title'    => $this->faker->realText($this->faker->numberBetween(10, 250)),
                'url'      => $isUrl ? $this->faker->url : null,
                'body'     => $body,
                'magazine' => $this->getReference('magazine_'.rand(1, MagazineFixtures::MAGAZINES_COUNT)),
                'user'     => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
            ];
        }
    }
}

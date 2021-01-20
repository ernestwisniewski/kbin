<?php declare(strict_types=1);

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Entry;

class EntryFixtures extends BaseFixture implements DependentFixtureInterface
{
    const ENTRIES_COUNT = 50;

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

    public function getDependencies()
    {
        return [
            MagazineFixtures::class,
        ];
    }

    private function provideRandomEntries($count = 1): iterable
    {
        for ($i = 0; $i <= $count; $i++) {
            $url  = rand(0, 1);
            $body = $this->faker->paragraphs(3, true);
            if ($url) {
                $body = rand(0, 1) ? $this->faker->paragraphs(3, true) : null;
            }
            yield [
                'title'    => $this->faker->sentence(rand(3, 25)),
                'url'      => $url ? $this->faker->url : null,
                'body'     => $body,
                'magazine' => $this->getReference('magazine_'.rand(1, MagazineFixtures::MAGAZINES_COUNT)),
                'user'     => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
            ];
        }
    }
}

<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Service\EntryManager;
use App\DTO\EntryDto;

class EntryFixtures extends BaseFixture implements DependentFixtureInterface
{
    const ENTRIES_COUNT = MagazineFixtures::MAGAZINES_COUNT * 15;

    private EntryManager $entryManager;

    public function __construct(EntryManager $entryManager)
    {
        $this->entryManager = $entryManager;
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
            $dto = (new EntryDto())->create($entry['title'], $entry['url'], $entry['body'], $entry['magazine']);

            $entity = $this->entryManager->createEntry($dto, $entry['user']);

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
                'title'    => $this->faker->realText($this->faker->numberBetween(10, 250)),
                'url'      => $isUrl ? $this->faker->url : null,
                'body'     => $body,
                'magazine' => $this->getReference('magazine_'.rand(1, MagazineFixtures::MAGAZINES_COUNT)),
                'user'     => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
            ];
        }
    }
}

<?php declare(strict_types = 1);

namespace App\DataFixtures;

use App\DTO\PostDto;
use App\Service\PostManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends BaseFixture implements DependentFixtureInterface
{
    const ENTRIES_COUNT = MagazineFixtures::MAGAZINES_COUNT * 15;

    public function __construct(
        private PostManager $postManager,
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
        foreach ($this->provideRandomPosts(self::ENTRIES_COUNT) as $index => $post) {
            $dto           = new PostDto();
            $dto->magazine = $post['magazine'];
            $dto->user     = $post['user'];
            $dto->body     = $post['body'];
            $dto->ip       = $post['ip'];

            $entity = $this->postManager->create($dto, $post['user']);

            $entity->createdAt = $this->getRandomTime();
            $entity->updateCounts();
            $entity->updateLastActive();
            $entity->updateRanking();

            $this->addReference('post'.'_'.$index, $entity);
        }

        $manager->flush();
    }

    private function provideRandomPosts($count = 1): iterable
    {
        for ($i = 0; $i <= $count; $i++) {
            yield [
                'body'     => $this->faker->realText($this->faker->numberBetween(10, 1024)),
                'magazine' => $this->getReference('magazine_'.rand(1, intval(MagazineFixtures::MAGAZINES_COUNT))),
                'user'     => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                'ip'       => $this->faker->ipv4,
            ];
        }
    }
}

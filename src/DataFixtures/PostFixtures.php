<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Kbin\Post\DTO\PostDto;
use App\Kbin\Post\PostCreate;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends BaseFixture implements DependentFixtureInterface
{
    public const ENTRIES_COUNT = MagazineFixtures::MAGAZINES_COUNT * 15;

    public function __construct(
        private readonly PostCreate $postCreate,
        private readonly ImageManager $imageManager,
        private readonly ImageRepository $imageRepository,
        private readonly EntityManagerInterface $entityManager
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
            $dto = new PostDto();
            $dto->magazine = $post['magazine'];
            $dto->user = $post['user'];
            $dto->body = $post['body'];
            $dto->ip = $post['ip'];
            $dto->lang = 'en';

            $entity = ($this->postCreate)($dto, $post['user']);

            $roll = rand(1, 400);
            if ($roll % 7) {
                try {
                    $tempFile = $this->imageManager->download("https://picsum.photos/300/$roll?hash=$roll");
                } catch (\Exception $e) {
                    $tempFile = null;
                }

                if ($tempFile) {
                    $image = $this->imageRepository->findOrCreateFromPath($tempFile);

                    $entity->image = $image;
                    $this->entityManager->flush();
                }
            }

            $entity->createdAt = $this->getRandomTime();
            $entity->updateCounts();
            $entity->updateLastActive();
            $entity->updateRanking();

            $this->addReference('post_'.$index, $entity);
        }

        $manager->flush();
    }

    private function provideRandomPosts($count = 1): iterable
    {
        for ($i = 0; $i <= $count; ++$i) {
            yield [
                'body' => $this->faker->realText($this->faker->numberBetween(10, 1024)),
                'magazine' => $this->getReference('magazine_'.rand(1, \intval(MagazineFixtures::MAGAZINES_COUNT))),
                'user' => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                'ip' => $this->faker->ipv4,
            ];
        }
    }
}

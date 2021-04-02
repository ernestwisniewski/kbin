<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\PostDto;
use App\Service\PostManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use App\Service\EntryManager;
use App\DTO\EntryDto;

class PostFixtures extends BaseFixture implements DependentFixtureInterface
{
    const ENTRIES_COUNT = MagazineFixtures::MAGAZINES_COUNT * 15;

    public function __construct(
        private PostManager $postManager,
        private ImageManager $imageManager,
        private ImageRepository $imageRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getDependencies()
    {
        return [
            MagazineFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        foreach ($this->provideRandomPosts(self::ENTRIES_COUNT) as $index => $post) {
            $dto = (new PostDto())->create($post['magazine'], $post['body']);

            $entity = $this->postManager->create($dto, $post['user']);

//            $roll = rand(100, 500);
//            if ($roll % 5) {
//                $tempFile = $this->imageManager->download("https://picsum.photos/300/$roll?hash=$roll");
//                $image    = $this->imageRepository->findOrCreateFromPath($tempFile);
//
//                $entity->setImage($image);
//                $this->entityManager->flush();
//            }

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
            ];
        }
    }
}

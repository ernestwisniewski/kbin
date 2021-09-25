<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends BaseFixture
{
    const USERS_COUNT = 21;

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private ImageManager $imageManager,
        private ImageRepository $imageRepository
    ) {
    }

    public function loadData(ObjectManager $manager): void
    {
        foreach ($this->provideRandomUsers(self::USERS_COUNT) as $index => $user) {
            $newUser = new User(
                $user['email'],
                $user['username'],
                $user['password']
            );

            $newUser->setPassword(
                $this->hasher->hashPassword($newUser, $user['password'])
            );

            $manager->persist($newUser);

            $this->addReference('user'.'_'.$index, $newUser);

            $manager->flush();

            if ($index > 1) {
                $rand     = rand(1, 500);
                $tempFile = $this->imageManager->download("https://picsum.photos/500/500?hash={$rand}");
                if ($tempFile) {
                    $image = $this->imageRepository->findOrCreateFromPath($tempFile);
                    $newUser->avatar = $image;
                    $manager->flush();
                }
            }
        }

    }

    private function provideRandomUsers($count = 1): iterable
    {
        yield [
            'email'    => 'demo@karab.in',
            'username' => 'demo',
            'password' => 'demo',
        ];

        for ($i = 0; $i <= $count; $i++) {
            yield [
                'email'    => $this->faker->email,
                'username' => str_replace('.', '_', $this->faker->userName),
                'password' => 'secret',
            ];
        }
    }
}

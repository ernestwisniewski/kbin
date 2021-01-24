<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends BaseFixture
{
    const USERS_COUNT = 5;

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
        foreach ($this->provideRandomUsers(self::USERS_COUNT) as $index => $user) {
            $newUser = new User(
                $user['email'],
                $user['username'],
                $user['password']
            );

            $newUser->setPassword(
                $this->encoder->encodePassword($newUser, $user['password'])
            );

            $manager->persist($newUser);

            $this->addReference('user'.'_'.$index, $newUser);
        }

        $manager->flush();
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
                'username' => $this->faker->userName,
                'password' => 'secret',
            ];
        }
    }
}

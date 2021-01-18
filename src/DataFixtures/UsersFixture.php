<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UsersFixture extends BaseFixture
{
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
        foreach ($this->provideRandomUsers(5) as $user) {
            $newUser = new User(
                $user['email'],
                $user['username'],
                $user['password']
            );

            $newUser->setPassword(
                $this->encoder->encodePassword($newUser, $user['password'])
            );

            $this->addReference('user-'.$user['username'], $newUser);

            $manager->persist($newUser);
        }

        $manager->flush();
    }

    private function provideRandomUsers($count = 1): iterable
    {
        for ($i = 0; $i < $count; $i++) {
            yield [
                'email'    => $this->faker->email,
                'username' => $this->faker->userName,
                'password' => 'secret',
            ];
        }
    }
}

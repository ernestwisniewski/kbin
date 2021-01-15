<?php

namespace App\DataFixtures\Tests;

use App\DataFixtures\Tests\BaseTestFixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class LoadExampleUsers extends BaseTestFixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->provideUsers() as $data) {
            $user = new User($data['email'], $data['username'], $data['password']);

            $this->addReference('user-'.$data['username'], $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function provideUsers(): iterable
    {
        yield [
            'username' => 'adminUser',
            'password' => 'adminUser123',
            'email'    => 'adminUser@example.com',
        ];

        yield [
            'username' => 'regularUser',
            'password' => 'regularUser123',
            'email'    => 'regularUser@example.com',
        ];
    }
}

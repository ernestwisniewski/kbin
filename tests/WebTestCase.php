<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var $users ArrayCollection
     */
    protected $users;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->users = new ArrayCollection();
    }

    protected function loadUsers(): void
    {
        $manager = self::$container->get(EntityManagerInterface::class);

        $this->users = new ArrayCollection();

        foreach ($this->provideUsers() as $data) {
            $user = new User($data['email'], $data['username'], $data['password']);

            $manager->persist($user);

            $this->users->add($user);
        }

        $manager->flush();
    }

    protected function getRegularUser(): User
    {
        if ($this->users->isEmpty()) {
            $this->loadUsers();
        }

        return $this->users->filter(
            static function (User $user) {
                return $user->getUsername() === 'regularUser';
            }
        )->first();
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

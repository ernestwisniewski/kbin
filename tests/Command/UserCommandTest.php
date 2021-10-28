<?php declare(strict_types=1);

namespace App\Tests\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class UserCommandTest extends KernelTestCase
{
    private Command $command;
    private ?UserRepository $repository;

    protected function setUp(): void
    {
        $application = new Application(self::bootKernel());

        $this->command    = $application->find('kbin:user:create');
        $this->repository = static::getContainer()->get(UserRepository::class);
    }

    public function testCreateUser()
    {
        $tester = new CommandTester($this->command);
        $tester->execute(
            [
                'username' => 'actor',
                'email'    => 'contact@example.com',
                'password' => 'secret',
            ]
        );

        $this->assertStringContainsString('A user has been created.', $tester->getDisplay());
        $this->assertInstanceOf(User::class, $this->repository->findOneByUsername('actor'));;
    }
}

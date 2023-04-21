<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Entity\User;
use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class RegisterControllerTest extends WebTestCase
{
    public function testUserCanVerifyAccount(): void
    {
        $client = $this->createClient();

        $this->registerUserAccount($client);

        $this->assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = $this->getMailerMessage();

        $this->assertEmailHeaderSame($email, 'To', 'johndoe@kbin.pub');

        $verifyLink = $email->getContext()['signedUrl'];

        $client->request('GET', $verifyLink);
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Log in')->form(
                [
                    'email' => 'JohnDoe',
                    'password' => 'secret',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextNotContains('#header', 'Log in');
    }

    private function registerUserAccount(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', '/register');

        $client->submit(
            $crawler->filter('form[name=user_register]')->selectButton('Register')->form(
                [
                    'user_register[username]' => 'JohnDoe',
                    'user_register[email]' => 'johndoe@kbin.pub',
                    'user_register[plainPassword][first]' => 'secret',
                    'user_register[plainPassword][second]' => 'secret',
                    'user_register[agreeTerms]' => true,
                ]
            )
        );
    }

    public function testUserCannotLoginWithoutConfirmation()
    {
        $client = $this->createClient();

        $this->registerUserAccount($client);

        $crawler = $client->followRedirect();

        $crawler = $client->click($crawler->filter('#header')->selectLink('Log in')->link());

        $client->submit(
            $crawler->selectButton('Log in')->form(
                [
                    'email' => 'JohnDoe',
                    'password' => 'wrong_password',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.alert__danger', 'Your account is not active.');
    }

    public static function register($active = false): KernelBrowser
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/register');

        $client->submit(
            $crawler->filter('form[name=user_register]')->selectButton('Register')->form(
                [
                    'user_register[username]' => 'JohnDoe',
                    'user_register[email]' => 'johndoe@kbin.pub',
                    'user_register[plainPassword][first]' => 'secret',
                    'user_register[plainPassword][second]' => 'secret',
                    'user_register[agreeTerms]' => true,
                ]
            )
        );

        if ($active) {
            $user = self::getContainer()->get('doctrine')->getRepository(User::class)
                ->findOneBy(['username' => 'JohnDoe']);
            $user->isVerified = true;

            self::getContainer()->get('doctrine')->getManager()->flush();
            self::getContainer()->get('doctrine')->getManager()->refresh($user);
        }

        return $client;
    }
}

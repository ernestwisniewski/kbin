<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Profile;

use App\Repository\UserRepository;
use App\Tests\Functional\Controller\Security\RegisterControllerTest;
use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserEditControllerTest extends WebTestCase
{
    public function testUserCanSeeSettingsLink(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('#header menu')->selectLink('Settings')->link());

        $this->assertSelectorTextContains('#main .options__main a.active', 'general');
    }

    public function testUserCanEditProfile(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', '/settings/profile');
        $this->assertSelectorTextContains('#main .options__main a.active', 'profile');

        $client->submit(
            $crawler->filter('#main form[name=user_basic]')->selectButton('Save')->form([
                'user_basic[about]' => 'test about',
            ])
        );

        $client->followRedirect();
        $this->assertSelectorTextContains('#main .user-box', 'test about');

        $client->request('GET', '/people');

        $this->assertSelectorTextContains('#main .user-box', 'JohnDoe');
    }

    public function testUserCanChangePassword(): void
    {
        $client = RegisterControllerTest::register(true);

        $client->loginUser($this->getService(UserRepository::class)->findOneBy(['username' => 'JohnDoe']));

        $crawler = $client->request('GET', '/settings/password');
        $this->assertSelectorTextContains('#main .options__main a.active', 'password');

        $client->submit(
            $crawler->filter('#main form[name=user_password]')->selectButton('Save')->form([
                'user_password[currentPassword]' => 'secret',
                'user_password[plainPassword][first]' => 'test123',
                'user_password[plainPassword][second]' => 'test123',
            ])
        );

        $client->followRedirect();

        $crawler = $client->request('GET', '/login');

        $client->submit(
            $crawler->filter('#main form')->selectButton('Log in')->form([
                'email' => 'JohnDoe',
                'password' => 'test123',
            ])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#header', 'JohnDoe');
    }

    public function testUserCanChangeEmail(): void
    {
        $client = RegisterControllerTest::register(true);

        $client->loginUser($this->getService(UserRepository::class)->findOneBy(['username' => 'JohnDoe']));

        $crawler = $client->request('GET', '/settings/email');
        $this->assertSelectorTextContains('#main .options__main a.active', 'email');

        $client->submit(
            $crawler->filter('#main form[name=user_email]')->selectButton('Save')->form([
                'user_email[newEmail][first]' => 'acme@kbin.pub',
                'user_email[newEmail][second]' => 'acme@kbin.pub',
                'user_email[currentPassword]' => 'secret',
            ])
        );

        $this->assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = $this->getMailerMessage();

        $this->assertEmailHeaderSame($email, 'To', 'acme@kbin.pub');

        $verifyLink = [];
        preg_match('#<a href="(?P<link>.+)">#', $email->getHtmlBody(), $verifyLink);

        $client->request('GET', $verifyLink['link']);

        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->filter('#main form')->selectButton('Log in')->form([
                'email' => 'JohnDoe',
                'password' => 'secret',
            ])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#header', 'JohnDoe');
    }
}

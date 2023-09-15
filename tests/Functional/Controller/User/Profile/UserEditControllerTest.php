<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Profile;

use App\Repository\UserRepository;
use App\Tests\Functional\Controller\Security\RegisterControllerTest;
use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserEditControllerTest extends WebTestCase
{
    public string $kibbyPath;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->kibbyPath = \dirname(__FILE__, 5).'/assets/kibby_emoji.png';
    }

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

    public function testUserCanUploadAvatar(): void
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        $repository = $this->getService(UserRepository::class);

        $crawler = $client->request('GET', '/settings/profile');
        $this->assertSelectorTextContains('#main .options__main a.active', 'profile');
        $this->assertStringContainsString('/dev/random', $user->avatar->filePath);

        $form = $crawler->filter('#main form[name=user_basic]')->selectButton('Save')->form();
        $form['user_basic[avatar]']->upload($this->kibbyPath);
        $client->submit($form);

        $user = $repository->find($user->getId());
        $this->assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $user->avatar->filePath);
    }

    public function testUserCanUploadCover(): void
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        $repository = $this->getService(UserRepository::class);

        $crawler = $client->request('GET', '/settings/profile');
        $this->assertSelectorTextContains('#main .options__main a.active', 'profile');
        $this->assertNull($user->cover);

        $form = $crawler->filter('#main form[name=user_basic]')->selectButton('Save')->form();
        $form['user_basic[cover]']->upload($this->kibbyPath);
        $client->submit($form);

        $user = $repository->find($user->getId());
        $this->assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $user->cover->filePath);
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

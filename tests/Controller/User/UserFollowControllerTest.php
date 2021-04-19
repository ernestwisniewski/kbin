<?php declare(strict_types=1);

namespace App\Tests\Controller\User;

use App\Service\UserManager;
use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserFollowControllerTest extends WebTestCase
{
    public function testCanShowPublicProfile()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $entry = $this->getEntryByTitle('treść1');
        $entry = $this->getEntryByTitle('treść2');

        $crawler = $client->request('GET', '/u/regularUser');

        $this->assertCount(2, $crawler->filter('article.kbin-entry'));
    }

    public function testUserCanFollow()
    {
        $client  = $this->createClient();
        $manager = self::$container->get(UserManager::class);

        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');
        $user4 = $this->getUserByUsername('regularUser4');

        $magazine  = $this->getMagazineByName('polityka', $user2);
        $magazine2 = $this->getMagazineByName('kuchnia', $user2);

        $this->getEntryByTitle('treść 1', null, null, $magazine, $user2);
        $this->getEntryByTitle('treść 2', null, null, $magazine2, $user2);
        $this->getEntryByTitle('treść 3', null, null, $magazine, $user3);
        $this->getEntryByTitle('treść 4', null, null, $magazine2, $user3);
        $this->getEntryByTitle('treść 5', null, null, $magazine, $user4);
        $this->getEntryByTitle('treść 6', null, null, $magazine2, $user4);

        $manager->follow($user3, $user2);

        $crawler = $client->request('GET', '/u/regularUser2');

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '1');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-sub button')->selectButton('obserwuj')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '2');

        $crawler = $client->request('GET', '/sub');

        $this->assertSelectorTextContains('.kbin-entry-title', 'treść 1');
        $this->assertCount(2, $crawler->filter('.kbin-entry-title'));

        $crawler = $client->request('GET', '/u/regularUser2');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-sub button')->selectButton('obserwuj')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '1');
    }

    public function testUserCanBlock() //@todo
    {
        $client  = $this->createClient();
        $manager = self::$container->get(UserManager::class);

        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');
        $user4 = $this->getUserByUsername('regularUser4');

        $magazine  = $this->getMagazineByName('polityka', $user2);
        $magazine2 = $this->getMagazineByName('kuchnia', $user2);

        $this->getEntryByTitle('treść 1', null, null, $magazine, $user2);
        $this->getEntryByTitle('treść 2', null, null, $magazine2, $user2);
        $this->getEntryByTitle('treść 3', null, null, $magazine, $user3);
        $this->getEntryByTitle('treść 4', null, null, $magazine2, $user3);
        $this->getEntryByTitle('treść 5', null, null, $magazine, $user4);
        $this->getEntryByTitle('treść 6', null, null, $magazine2, $user4);

        $manager->follow($user, $user2);

        $crawler = $client->request('GET', '/u/regularUser2');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-user-block button')->selectButton('')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('kbin-block--active', $crawler->filter('.kbin-user-block')->attr('class'));
        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '0');
    }

    public function testUserCanChangePassword()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbn-login-btn')->selectLink('Profil')->link());
        $crawler = $client->click($crawler->filter('.kbin-main')->selectLink('Edytuj profil')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'user[plainPassword][first]'  => 'supersecret',
                    'user[plainPassword][second]' => 'supersecret',
                    'user[agreeTerms]'            => true,
                ]
            )
        );

        $crawler = $client->followRedirect();
        $crawler = $client->followRedirect();
        $crawler = $client->request('GET', '/wyloguj');
        $crawler = $client->followRedirect();

        $crawler = $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email'    => 'testUser@example.com',
                    'password' => 'supersecret',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbn-login-btn', 'Profil');
    }

    public function testUserCanChangeEmail()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbn-login-btn')->selectLink('Profil')->link());
        $crawler = $client->click($crawler->filter('.kbin-main')->selectLink('Edytuj profil')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'user[email]'                 => 'ernest@karab.in',
                    'user[agreeTerms]'            => true,
                    'user[plainPassword][first]'  => 'secret',
                    'user[plainPassword][second]' => 'secret',
                ]
            )
        );

        $this->assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = $this->getMailerMessage(0);

        $this->assertEmailHeaderSame($email, 'To', 'ernest@karab.in');

        $verifyLink = $email->getContext()['signedUrl'];

        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', '/wyloguj');
        $crawler = $client->followRedirect();

        $crawler = $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email'    => 'ernest@karab.in',
                    'password' => 'secret',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.alert-danger', 'Twoje konto nie jest aktywne.');

        $crawler = $client->request('GET', $verifyLink);
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email'    => 'ernest@karab.in',
                    'password' => 'secret',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbn-login-btn', 'Profil');
    }
}

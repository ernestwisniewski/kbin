<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserEditControllerTest extends WebTestCase
{
    public function testUserCanChangePassword(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-profile-btn')->link());
        $crawler = $client->click($crawler->filter('.nav-link')->selectLink('Edytuj profil')->link());

        $client->submit(
            $crawler->filter('form[name=user_password]')->selectButton('Gotowe')->form(
                [
                    'user_password[plainPassword][first]' => 'supersecret',
                    'user_password[plainPassword][second]' => 'supersecret',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email' => 'testUser@example.com',
                    'password' => 'supersecret',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorExists('.kbin-profile-btn');
    }

    public function testUserCanChangeEmail(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-profile-btn')->link());
        $crawler = $client->click($crawler->filter('.nav-link')->selectLink('Edytuj profil')->link());

        $client->submit(
            $crawler->filter('form[name=user_email]')->selectButton('Gotowe')->form(
                [
                    'user_email[email]' => 'ernest@karab.in',

                ]
            )
        );

        $this->assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = $this->getMailerMessage(0);

        $this->assertEmailHeaderSame($email, 'To', 'ernest@karab.in');

        $verifyLink = $email->getContext()['signedUrl'];

        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email' => 'ernest@karab.in',
                    'password' => 'secret',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.alert-danger', 'Twoje konto nie jest aktywne.');

        $client->request('GET', $verifyLink);
        $client->followRedirect();

        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email' => 'ernest@karab.in',
                    'password' => 'secret',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextNotContains('.alert-danger', 'Twoje konto nie jest aktywne.');
    }
}

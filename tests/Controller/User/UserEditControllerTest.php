<?php declare(strict_types=1);

namespace App\Tests\Controller\User;

use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserEditControllerTest extends WebTestCase
{
    public function testUserCanChangePassword()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbn-login-btn')->selectLink('Profil')->link());
        $crawler = $client->click($crawler->filter('.kbin-main')->selectLink('Edytuj profil')->link());

        $client->submit(
            $crawler->filter('button#user_password_submit')->form(
                [
                    'user_password[plainPassword][first]'  => 'supersecret',
                    'user_password[plainPassword][second]' => 'supersecret',
                ]
            )
        );

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
            $crawler->filter('button#user_email_submit')->form(
                [
                    'user_email[email]' => 'ernest@karab.in',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = $this->getMailerMessage(0);

        $this->assertEmailHeaderSame($email, 'To', 'ernest@karab.in');

        $verifyLink = $email->getContext()['signedUrl'];

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
